import axios from "axios";
// console.error('error admin');
const switchs = document.querySelectorAll("[data-switch-active-tag]");

if (switchs) {
  switchs.forEach((element) => {
    element.addEventListener("change", () => {
      let tagId = element.value;
      axios.get(`/admin/article/switch/${tagId}`);
    });
  });
}