let availableCourses = [];
let courseSearchQuery = '';
let enrolledCourseIds = new Set();
let currentCourseCount = 0;

document.addEventListener("DOMContentLoaded", loadCourses);

async function loadCourses() {
    const [coursesResult, myCoursesResult] = await Promise.all([
        getData("CourseController.php?action=list"),
        getData("CourseController.php?action=my-courses")
    ]);

    if (!coursesResult.success) {
        console.error('Course list load failed:', coursesResult);
        const container = document.getElementById('courseList');
        container.innerHTML = `<p>${escapeHTML(coursesResult.message || 'Unable to load courses.')}</p>`;
        return;
    }

    availableCourses = Array.isArray(coursesResult.data) ? coursesResult.data : [];
    if (myCoursesResult.success && Array.isArray(myCoursesResult.data)) {
        enrolledCourseIds = new Set(myCoursesResult.data.map(course => course.id));
        currentCourseCount = myCoursesResult.data.length;
    } else {
        enrolledCourseIds = new Set();
        currentCourseCount = 0;
    }

    renderCourses(availableCourses);
}

function renderCourses(courses) {
    const container = document.getElementById("courseList");
    container.innerHTML = '';

    if (!Array.isArray(courses) || courses.length === 0) {
        container.innerHTML = '<p>No courses match your search.</p>';
        return;
    }

    courses.forEach(course => {
        const div = document.createElement("div");
        div.classList.add("course-card");

        div.innerHTML = `
            <div class="course-card-body">
                <h3>${highlightMatch(`${course.code} — ${course.title}`, courseSearchQuery)}</h3>
                <p>${highlightMatch(course.description, courseSearchQuery)}</p>
                <div class="course-meta">
                    <span>${highlightMatch(course.instructor, courseSearchQuery)}</span>
                    <span>${course.credits} credits</span>
                    <span>${course.schedule || ''}</span>
                </div>
                <button
                    class="enroll-btn"
                    data-id="${course.id}"
                    ${enrolledCourseIds.has(course.id) || currentCourseCount >= 3 ? 'disabled' : ''}
                    title="${enrolledCourseIds.has(course.id) ? 'Already enrolled' : currentCourseCount >= 3 ? 'Maximum of 3 courses reached' : 'Enroll in this course'}"
                >
                    ${enrolledCourseIds.has(course.id) ? 'Enrolled' : currentCourseCount >= 3 ? 'Max reached' : 'Enroll'}
                </button>
            </div>
        `;

        container.appendChild(div);
    });

    attachEnrollEvents();
}

function escapeHTML(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function highlightMatch(value, query) {
    const text = escapeHTML(value);
    if (!query) return text;
    const pattern = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`(${pattern})`, 'gi');
    return text.replace(regex, '<mark class="highlight">$1</mark>');
}

function filterCourses() {
    const query = document.getElementById('searchInput')?.value.trim().toLowerCase();
    courseSearchQuery = query;
    if (!query) {
        renderCourses(availableCourses);
        return;
    }

    const filtered = availableCourses.filter(course => {
        return [course.title, course.code, course.instructor, course.description]
            .filter(Boolean)
            .some(field => field.toLowerCase().includes(query));
    });

    renderCourses(filtered);
}

function attachEnrollEvents() {
    document.querySelectorAll(".enroll-btn").forEach(btn => {
        btn.addEventListener("click", async () => {
            const courseId = btn.dataset.id;
            btn.disabled = true;
            const result = await postData("CourseController.php?action=enroll", {
                course_id: courseId
            });

            alert(result.message);
            if (result.success) {
                btn.textContent = 'Enrolled';
                btn.classList.add('disabled');
            } else {
                btn.disabled = false;
            }
        });
    });
}