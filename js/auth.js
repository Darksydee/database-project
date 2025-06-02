function checkLoginUser() {
    if (!localStorage.getItem("user")) {
      window.location.href = "login.html";
    }
  }
  function checkLoginAdmin() {
    const user = JSON.parse(localStorage.getItem("user"));
    if (!user || user.role !== "admin") {
      window.location.href = "login.html";
    }
  }
  
