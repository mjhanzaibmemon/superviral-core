const popup = document.getElementById("uk-popup");
// isOverflowHiddenBefore is used to check if the body overflow was hidden before the popup was opened
let isOverflowHiddenBefore = false;

// Show flag
function flagShow(country) {
  try {
    const flagIcon = document.getElementById("flag-icon-c");
    if (flagIcon) {
      // Update the flag image
      flagIcon.innerHTML = `<img src="https://flagcdn.com/${country.toLowerCase()}.svg" alt="${country} flag" />`;
      flagIcon.style.display = "flex";
    }
  } catch (error) {
    console.error("Error showing flag:", error);
  }
}

const openPopup = () => {
  try {
    if (popup) {
      popup.style.display = "flex";
      isOverflowHiddenBefore = document.body.style.overflowY === "hidden";
      document.body.style.overflowY = "hidden";
    }
  } catch (error) {
    console.error("Error opening popup:", error);
  }
};

const closePopup = () => {
  try {
    if (popup) {
      popup.style.display = "none";
      if (!isOverflowHiddenBefore) {
        document.body.style.overflowY = "auto";
      }
      // Mark popup as seen
      localStorage.setItem('ukPopupSeen', 'true');
    }
  } catch (error) {
    console.error("Error closing popup:", error);
  }
};

// Initialize popup
const initializePopup = () => {
  try {
    if (popup) {
      // popup.addEventListener("click", function (event) {
      //   if (event.target === popup) {
      //     closePopup();
      //   }
      // });
      document.getElementById("close-uk-popup").addEventListener("click", function () {
        closePopup();
      });
    }
  } catch (error) {
    console.error("Error initializing popup:", error);
  }
};

// Detect country and show flag and open popup if user is from UK
function detectCountry() {
  try {
     // return if URL contains /uk/
     if (window.location.pathname.includes('/uk/')) {
      return;
    }
    initializePopup();
    let isUk = false;
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "?get_country=1", true);
    xhr.timeout = 4000;

    xhr.onreadystatechange = function () {
      try {
        if (xhr.readyState === 4 && xhr.status === 200) {
          const country = xhr.responseText;
          if (
            country &&
            typeof country === "string" &&
            /^[a-zA-Z]{2}$/.test(country)
          ) {
            isUk = country === "GB";
            flagShow(country);
          }
        }
      } catch (error) {
        console.error("Country Detector: Error processing response:", error);
      }
    };

    xhr.onerror = function () {
      console.error("Country Detector: Network error occurred");
    };

    xhr.send();

    // Open popup after 5 seconds
    setTimeout(function () {
      try {
        // Open popup if user is from UK and hasn't seen it before
        if (isUk && !localStorage.getItem('ukPopupSeen')) {
          openPopup();
        }
      } catch (error) {
        console.error("Country Detector: Error in timeout callback:", error);
      }
    }, 5000);
  } catch (error) {
    console.error("Country Detector: Error in detectCountry function:", error);
  }
}

// Auto-start when page loads with error handling
try {
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      detectCountry();
    });
  } else {
    detectCountry();
  }
} catch (error) {
  console.error("Country Detector: Error during initialization:", error);
}
