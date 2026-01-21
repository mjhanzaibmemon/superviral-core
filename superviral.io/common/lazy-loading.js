// Lazy loading
document.addEventListener("DOMContentLoaded", function() {
    try {
        // Select all lazy load images and background elements
        let lazyElements = [].slice.call(document.querySelectorAll("img.lazy, .lazy-bg"));

        if ("IntersectionObserver" in window) {
            let lazyElementObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyElement = entry.target;
                        if (lazyElement.tagName === 'IMG') {
                            lazyElement.src = lazyElement.getAttribute("data-src");
                            lazyElement.classList.add("lazy-loaded");
                        } else if (lazyElement.classList.contains("lazy-bg")) {
                            lazyElement.style.backgroundImage = `url(${lazyElement.getAttribute("data-bg-src")})`;
                            lazyElement.classList.add("lazy-bg-loaded");
                        }
                        lazyElementObserver.unobserve(lazyElement);
                    }
                });
            });

            lazyElements.forEach(function(lazyElement) {
                lazyElementObserver.observe(lazyElement);
            });

        } else {
            let active = false;

            const lazyLoad = function() {
                if (active === false) {
                    active = true;

                    setTimeout(function() {
                        lazyElements.forEach(function(lazyElement) {
                            if ((lazyElement.getBoundingClientRect().top <= window.innerHeight && lazyElement.getBoundingClientRect().bottom >= 0) && getComputedStyle(lazyElement).display !== "none") {
                                if (lazyElement.tagName === 'IMG') {
                                    lazyElement.src = lazyElement.getAttribute("data-src");
                                    lazyElement.classList.add("lazy-loaded");
                                } else if (lazyElement.classList.contains("lazy-bg")) {
                                    lazyElement.style.backgroundImage = `url(${lazyElement.getAttribute("data-bg-src")})`;
                                    lazyElement.classList.add("lazy-bg-loaded");
                                }

                                lazyElements = lazyElements.filter(function(element) {
                                    return element !== lazyElement;
                                });

                                if (lazyElements.length === 0) {
                                    document.removeEventListener("scroll", lazyLoad);
                                    window.removeEventListener("resize", lazyLoad);
                                    window.removeEventListener("orientationchange", lazyLoad);
                                }
                            }
                        });

                        active = false;
                    }, 200);
                }
            };

            document.addEventListener("scroll", lazyLoad);
            window.addEventListener("resize", lazyLoad);
            window.addEventListener("orientationchange", lazyLoad);
        }
    } catch (error) {
        console.error(error);
    }
});