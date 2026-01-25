// Tab persistence for settings page
document.addEventListener('DOMContentLoaded', function() {
    // Check for active tab from server
    const container = document.querySelector('.container[data-active-tab]');
    const activeTab = container ? container.getAttribute('data-active-tab') : 'departments';

    // Activate the tab
    const tabLink = document.querySelector(`a[href="#${activeTab}"]`);
    if (tabLink) {
        $(tabLink).tab('show');
    }
});