// FAQ
const faqBoxes = document.querySelectorAll(".faq .faq-part .box");
faqBoxes.forEach((box)=>{
    // const icon = box.querySelector(".question .icon");
    box.addEventListener("click", ()=>{
        box.classList.toggle("active");
    })
})
