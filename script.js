document.querySelectorAll(".tab").forEach((tab) => {
    tab.addEventListener("click", function () {
      document
        .querySelectorAll(".tab")
        .forEach((t) => t.classList.remove("active"));

      this.classList.add("active");

      const tabBg = document.querySelector(".tab-bg");
      tabBg.style.left = this.offsetLeft + "px";
      tabBg.style.width = this.offsetWidth + "px";
    });
  });

  // Pastikan elemen dengan kelas 'password-toggle' ada sebelum menambahkan event listener
  const passwordToggleButton = document.querySelector(".password-toggle");
  if (passwordToggleButton) {
    passwordToggleButton.addEventListener("click", function () {
      // Menggunakan ID yang benar dari input password di login.php yaitu "inputPassword"
      const passwordInput = document.getElementById("inputPassword");
      if (passwordInput) {
        const type =
          passwordInput.getAttribute("type") === "password" ? "text" : "password";
        passwordInput.setAttribute("type", type);

        const eyeIcon = this.querySelector("i"); // 'this' merujuk pada passwordToggleButton
        if (eyeIcon) {
          if (type === "text") {
            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash");
          } else {
            eyeIcon.classList.remove("fa-eye-slash");
            eyeIcon.classList.add("fa-eye");
          }
        }
      } else {
        console.error("Elemen input password dengan ID 'inputPassword' tidak ditemukan.");
      }
    });
  } else {
    // Anda bisa menampilkan pesan di console jika tombol tidak ditemukan, untuk debugging di halaman lain
    // console.warn("Tombol '.password-toggle' tidak ditemukan di halaman ini.");
  }
  // Data fitur statistik trafik yang ingin diuji
const dataTrafik = {
  "Destination Port": 80,
  "Flow Duration": 1000,
  "Total Fwd Packets": 10,
  "Total Length of Fwd Packets": 500,
  "Fwd Packet Length Max": 100,
  "Fwd Packet Length Mean": 50,
  "Fwd Packet Length Std": 10,
  "FIN Flag Count": 0,
  "PSH Flag Count": 1,
  "ACK Flag Count": 0
};

// Panggil API Python di port 5000
fetch('http://127.0.0.1:5000/predict', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(dataTrafik)
})
.then(response => response.json())
.then(data => {
  cconsole.log("Hasil Prediksi:", data.result);
alert("Status Trafik: " + data.result);
})
.catch(error => console.error('Error:', error));