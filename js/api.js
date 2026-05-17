const pathSegments = window.location.pathname.split('/');
const uiIndex = pathSegments.indexOf('ui');
const appRoot = uiIndex > 0 ? pathSegments.slice(0, uiIndex).join('/') : '/AssignmentProject';
const API_BASE = `${window.location.origin}${appRoot}/controllers`;

async function postData(url, data) {
    if (!url) {
        throw new Error('Missing API endpoint.');
    }

    try {
        const response = await fetch(`${API_BASE}/${url}`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
            credentials: 'same-origin'
        });

        return await response.json();
    } catch (error) {
        return { success: false, message: 'Unable to contact server.' };
    }
}

async function getData(url) {
    try {
        const response = await fetch(`${API_BASE}/${url}`, {
            credentials: 'same-origin'
        });
        return await response.json();
    } catch (error) {
        return { success: false, message: 'Unable to contact server.' };
    }
}