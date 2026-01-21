// DOM Elements
const exportButton = document.getElementById('export-button');
const setThumbBtn = document.getElementById('set-thumb');


// Banner Elements
const banner = document.getElementById('editable-banner');
const bannerContent = document.getElementById('banner-content');
const innerImageContainer = document.getElementById('inner-image-container');

// Form Elements
const bgColorStart = document.getElementById('bg-color-start');
const bgColorEnd = document.getElementById('bg-color-end');
const htmlContent = document.getElementById('html-content');
const contentWidth = document.getElementById('content-width');
const imageWidth = document.getElementById('image-width');
const innerTop = document.getElementById('inner-top');
const innerRight = document.getElementById('inner-right');

// Initialize form values with current banner settings
function initializeFormValues() {
    // Update form fields with current inner image position
    const innerImageStyle = getComputedStyle(innerImageContainer);
    innerTop.value = parseInt(innerImageStyle.top) || 115;
    innerRight.value = parseInt(innerImageStyle.right) || 115;
    
    // Initialize content width - get actual width from computed style
    const bannerContentStyle = getComputedStyle(bannerContent);
    contentWidth.value = parseInt(bannerContentStyle.width) || 500;
    
    // Initialize image width
    imageWidth.value = parseInt(innerImageStyle.width) || 320;
    
    // Set initial HTML content
    // htmlContent.value = '';
    
    // Initial update of banner content
    updateBannerFromHTML();
}

// Setup live updating
function setupLiveUpdates() {
    // Background color updates
    bgColorStart.addEventListener('input', updateBanner);
    bgColorEnd.addEventListener('input', updateBanner);
    
    // HTML content updates
    htmlContent.addEventListener('input', updateBannerFromHTML);
    
    // Width and position updates
    contentWidth.addEventListener('input', updateBanner);
    imageWidth.addEventListener('input', updateBanner);
    innerTop.addEventListener('input', updateBanner);
    innerRight.addEventListener('input', updateBanner);
}

// Update banner from HTML content
function updateBannerFromHTML() {
    bannerContent.innerHTML = htmlContent.value;
    updateBanner();
}

// Update all banner elements
function updateBanner() {
    // Update background color
    banner.style.background = `linear-gradient(to right, ${bgColorStart.value}, ${bgColorEnd.value})`;
    
    // Update banner content width
    bannerContent.style.width = `${contentWidth.value}px`;
    
    // Update inner image width and position
    innerImageContainer.style.width = `${imageWidth.value}px`;
    innerImageContainer.style.height = `${imageWidth.value}px`; // Make it square
    innerImageContainer.style.top = `${innerTop.value}px`;
    innerImageContainer.style.right = `${innerRight.value}px`;
}

window.addEventListener('load', async () => {
    try {
        const parentDoc = window.parent.document;

        let edit = document.getElementById('edit_param').value;
        if(edit === 'true'){
            parentDoc.querySelector('.cover-spin').style.display = 'none';
            return;
        }
        parentDoc.querySelector('.cover-spin').style.display = 'flex';
        // Loading indicator
        setThumbBtn.textContent = 'Setting...';
        setThumbBtn.disabled = true;
        
        // Use html2canvas to convert the banner to an image
        const canvas = await html2canvas(banner, {
            scale: 2, // Higher resolution
            useCORS: true, // Allow images from other domains
            allowTaint: true,
            backgroundColor: null,
            width: 1080,
            height: 564
        });
        
        const dataURL = canvas.toDataURL('image/png');
        let article_id = document.getElementById('article_id').value;
        const response = await fetch('save_tn.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ image: dataURL, article_id: article_id })
        });

        const result = await response.json();
        if (result.success) {
            const hiddenInput = parentDoc.getElementById('txtCount');
            hiddenInput.value = parseInt(hiddenInput.value) + 1;
            console.log('Image saved to S3: ' + result.url);
        } else {
            parentDoc.querySelector('.cover-spin').style.display = 'none';
            alert(result.error);
        }

        // // Reset button
        setThumbBtn.textContent = 'Set Thumbnail';
        setThumbBtn.disabled = false;
    } catch (error) {
        console.error('Error exporting banner:', error);
        console.log('Failed to export banner. Please try again.');
        setThumbBtn.textContent = 'Set Thumbnail';
        setThumbBtn.disabled = false;
    } finally {
        setThumbBtn.textContent = 'Set Thumbnail';
        setThumbBtn.disabled = false;
    }
});


// Set Thumbnail
setThumbBtn.addEventListener('click', async () => {
    try {
        // Loading indicator
        setThumbBtn.textContent = 'Setting...';
        setThumbBtn.disabled = true;
        
        // Use html2canvas to convert the banner to an image
        const canvas = await html2canvas(banner, {
            scale: 2, // Higher resolution
            useCORS: true, // Allow images from other domains
            allowTaint: true,
            backgroundColor: null,
            width: 1080,
            height: 564
        });
        
        const dataURL = canvas.toDataURL('image/png');
        let article_id = document.getElementById('article_id').value;
        const response = await fetch('save_tn.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ image: dataURL, article_id: article_id })
        });

        const result = await response.json();
        if (result.success) {
            console.log('Image saved to S3: ' + result.url);
        } else {
            console.log(result.error);
        }

        // // Create a download link for the image
        // const link = document.createElement('a');
        // link.download = 'custom-banner.png';
        // link.href = canvas.toDataURL('image/png');
        // link.click();
        
        // // Reset button
        setThumbBtn.textContent = 'Set Thumbnail';
        setThumbBtn.disabled = false;
    } catch (error) {
        console.error('Error exporting banner:', error);
        console.log('Failed to export banner. Please try again.');
        setThumbBtn.textContent = 'Set Thumbnail';
        setThumbBtn.disabled = false;
    } finally {
        setThumbBtn.textContent = 'Set Thumbnail';
        setThumbBtn.disabled = false;
    }
});

// Export Banner
exportButton.addEventListener('click', async () => {
    try {
        // Loading indicator
        exportButton.textContent = 'Exporting...';
        exportButton.disabled = true;
        
        // Use html2canvas to convert the banner to an image
        const canvas = await html2canvas(banner, {
            scale: 2, // Higher resolution
            useCORS: true, // Allow images from other domains
            allowTaint: true,
            backgroundColor: null,
            width: 1080,
            height: 564
        });
        
        // Create a download link for the image
        const link = document.createElement('a');
        link.download = 'custom-banner.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
        
        // Reset button
        exportButton.textContent = 'Export Banner';
        exportButton.disabled = false;
    } catch (error) {
        console.error('Error exporting banner:', error);
        alert('Failed to export banner. Please try again.');
        exportButton.textContent = 'Export Banner';
        exportButton.disabled = false;
    }
});

// Make the inner image draggable (more intuitive than using number inputs)
let isDragging = false;
let dragStartX, dragStartY;
let initialTop, initialRight;

// Handle image dragging
innerImageContainer.addEventListener('mousedown', (e) => {
    isDragging = true;
    
    // Disable transitions for smoother dragging
    innerImageContainer.style.transition = 'none';
    
    // Get initial position
    const style = getComputedStyle(innerImageContainer);
    initialTop = parseInt(style.top) || 0;
    initialRight = parseInt(style.right) || 0;
    
    // Get initial cursor position
    dragStartX = e.clientX;
    dragStartY = e.clientY;
    
    // Prevent default behavior
    e.preventDefault();
});

document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    
    // Calculate the difference
    const deltaY = e.clientY - dragStartY;
    const deltaX = dragStartX - e.clientX; // Inverted for right property
    
    // Update position without animation
    innerImageContainer.style.top = `${initialTop + deltaY}px`;
    innerImageContainer.style.right = `${initialRight + deltaX}px`;
    
    // Update form values
    innerTop.value = initialTop + deltaY;
    innerRight.value = initialRight + deltaX;
});

function endDrag() {
    if (isDragging) {
        isDragging = false;
        // Re-enable transitions with a small delay
        setTimeout(() => {
            innerImageContainer.style.transition = 'width 0.3s ease, height 0.3s ease';
        }, 50);
    }
}

document.addEventListener('mouseup', endDrag);
document.addEventListener('mouseleave', endDrag);

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    initializeFormValues();
    setupLiveUpdates();
    updateBanner();
}); 