document.addEventListener("DOMContentLoaded", function () {
    renderMathInElement(document.body, {
      delimiters: [
        { left: "[math(", right: ")]", display: false },
        { left: "<math>", right: "</math>", display: false },
      ],
    });
  });