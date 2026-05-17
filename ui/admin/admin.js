async function ensureAdminSession() {
    const result = await getData('UserController.php?action=profile');
    if (!result.success || result.data?.role !== 'admin') {
        alert('Admin login required. Redirecting to login.');
        window.location.href = '../auth/login.html';
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!(await ensureAdminSession())) {
        return;
    }

    document.getElementById('courseForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const data = {
            code: document.getElementById('code').value,
            title: document.getElementById('title').value,
            description: document.getElementById('description').value,
            instructor: document.getElementById('instructor').value,
            credits: Number(document.getElementById('credits').value),
            capacity: Number(document.getElementById('capacity').value),
            schedule: document.getElementById('schedule').value,
            category: document.getElementById('category').value
        };

        const result = await postData('CourseController.php?action=add', data);

        if (result.success) {
            alert('Course saved successfully.');
            document.getElementById('courseForm').reset();
            return;
        }

        alert(result.message || 'Unable to save course.');
    });
});