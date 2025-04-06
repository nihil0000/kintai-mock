// require('./bootstrap');
import '../css/app.css';

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('image');
    const fileName = document.getElementById('file-name');

    if (input && fileName) {
        input.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            fileName.textContent = this.files[0].name;
        } else {
            fileName.textContent = '';
        }
        });
    }
});
