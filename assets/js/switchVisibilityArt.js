import axios from "axios";
// console.error('error admin');

export default function visibilityArticles() {
  const switchs = document.querySelectorAll("[data-switch-active-art]");

  if (switchs) {
    switchs.forEach((element) => {
      element.addEventListener("change", () => {
        let artId = element.value;
        axios.get(`/admin/switch/${artId}`);
      });
    });
  }
}
