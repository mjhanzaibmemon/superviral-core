function getOrCreateUserId() {
  let userId = localStorage.getItem('heap_userId');
  if (!userId) {
    userId = 'user-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('heap_userId', userId);
  }
  return userId;
}

function initializeHeapIdentification() {
  try {
    const userId = getOrCreateUserId();
    
    // Check if heap is available and ready
    if (typeof heap !== 'undefined' && heap.identify) {
      heap.identify(userId);
      console.log('Heap user identified:', userId);
    } else if (typeof heap !== 'undefined' && heap.onReady) {
      // Use heap's onReady method if available (recommended approach)
      heap.onReady(function() {
        heap.identify(userId);
        console.log('Heap user identified (onReady):', userId);
      });
    } else {
      // Fallback: retry after a short delay
      setTimeout(function() {
        if (typeof heap !== 'undefined' && heap.identify) {
          heap.identify(userId);
          console.log('Heap user identified (delayed):', userId);
        } else {
          console.warn('Heap.io not available for user identification');
        }
      }, 500);
    }
  } catch (error) {
    console.error('Error initializing heap identification:', error);
  }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeHeapIdentification);
} else {
  // DOM is already loaded, initialize immediately
  initializeHeapIdentification();
} 