let video = document.getElementById('video');
let studentInfo = document.getElementById('studentDetails');
let modelLoaded = false;
let lastDetectionTime = Date.now();
const DETECTION_INTERVAL = 100; // Detection interval in milliseconds
const MATCH_THRESHOLD = 0.5; // Lower threshold for better matching (default was 0.6)

// First load all required face-api.js models
async function loadModels() {
    try {
        // Load models in parallel for faster initialization
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri('/face_attendance/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/face_attendance/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/face_attendance/models')
        ]);
        console.log('Models loaded successfully');
        modelLoaded = true;
        startVideo();
    } catch (err) {
        console.error('Error loading models:', err);
    }
}

function startVideo() {
    // Request camera with specific constraints for better performance
    const constraints = {
        video: {
            width: { ideal: 640 }, // Reduced from 720
            height: { ideal: 480 }, // Reduced from 560
            facingMode: 'user',
            frameRate: { ideal: 30 }
        }
    };

    navigator.mediaDevices.getUserMedia(constraints)
        .then(stream => {
            video.srcObject = stream;
        })
        .catch(err => console.error('Error accessing camera:', err));
}

// Cache for student descriptors to avoid reloading
let cachedDescriptors = null;
let lastDescriptorLoad = 0;
const CACHE_DURATION = 60000; // Cache duration in milliseconds (1 minute)

async function loadLabeledImages() {
    try {
        // Check if we have valid cached descriptors
        if (cachedDescriptors && (Date.now() - lastDescriptorLoad < CACHE_DURATION)) {
            return cachedDescriptors;
        }

        const response = await fetch('get_registered_students.php');
        const students = await response.json();
        
        if (!students || students.length === 0) {
            console.log('No registered students found');
            return [];
        }

        const labeledDescriptors = [];
        
        // Process students in parallel for faster loading
        await Promise.all(students.map(async student => {
            try {
                const descriptions = [];
                const imgUrl = `/face_attendance/resources/faces/${student.nim}.jpg`;
                
                const img = await faceapi.fetchImage(imgUrl);
                const detection = await faceapi.detectSingleFace(img)
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                
                if (detection) {
                    descriptions.push(detection.descriptor);
                    labeledDescriptors.push(
                        new faceapi.LabeledFaceDescriptors(student.id.toString(), descriptions)
                    );
                }
            } catch (error) {
                console.error(`Error loading face for student ${student.nim}:`, error);
            }
        }));

        // Update cache
        cachedDescriptors = labeledDescriptors;
        lastDescriptorLoad = Date.now();
        
        return labeledDescriptors;
    } catch (error) {
        console.error('Error loading labeled images:', error);
        return [];
    }
}

video.addEventListener('play', async () => {
    if (!modelLoaded) {
        console.error('Models not loaded yet');
        return;
    }

    const canvas = faceapi.createCanvasFromMedia(video);
    document.querySelector('.video-container').append(canvas);
    const displaySize = { width: video.width, height: video.height };
    faceapi.matchDimensions(canvas, displaySize);

    try {
        const labeledDescriptors = await loadLabeledImages();
        
        if (labeledDescriptors.length === 0) {
            console.log('No face descriptors loaded. Please register students first.');
            return;
        }

        const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, MATCH_THRESHOLD);
        console.log('Face matcher created successfully');

        // Use requestAnimationFrame for smoother performance
        const detectFaces = async () => {
            const now = Date.now();
            if (now - lastDetectionTime >= DETECTION_INTERVAL) {
                try {
                    const detections = await faceapi.detectAllFaces(video)
                        .withFaceLandmarks()
                        .withFaceDescriptors();

                    if (detections.length > 0) {
                        const resizedDetections = faceapi.resizeResults(detections, displaySize);
                        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

                        const results = resizedDetections.map(d => 
                            faceMatcher.findBestMatch(d.descriptor)
                        );

                        results.forEach((result, i) => {
                            const box = resizedDetections[i].detection.box;
                            const drawBox = new faceapi.draw.DrawBox(box, { 
                                label: result.toString(),
                                boxColor: result.distance < MATCH_THRESHOLD ? '#4CAF50' : '#ff0000'
                            });
                            drawBox.draw(canvas);

                            if (result.distance < MATCH_THRESHOLD) {
                                fetchStudentInfo(result.label);
                            }
                        });
                    }
                    lastDetectionTime = now;
                } catch (error) {
                    console.error('Error in face detection:', error);
                }
            }
            requestAnimationFrame(detectFaces);
        };

        detectFaces();
    } catch (error) {
        console.error('Error in face detection setup:', error);
    }
});

// Debounce function to prevent too many API calls
const debounce = (func, wait) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
};

const fetchStudentInfo = debounce(async (studentId) => {
    try {
        const response = await fetch(`get_student_info.php?id=${studentId}`);
        const data = await response.json();
        
        if (data.error) {
            console.error('Error fetching student info:', data.error);
            return;
        }
        
        studentInfo.innerHTML = `
            <p><strong>NIM:</strong> ${data.nim}</p>
            <p><strong>Name:</strong> ${data.name}</p>
            <p><strong>Address:</strong> ${data.address}</p>
            <p><strong>Phone:</strong> ${data.phone}</p>
            <p><strong>Email:</strong> ${data.email}</p>
        `;
    } catch (error) {
        console.error('Error:', error);
    }
}, 500); // Wait 500ms between API calls

// Start loading models when the page loads
loadModels(); 