/**
 * Validasi client-side form pegawai
 */
function validasiFormPegawai(event) {
  const nip = document.getElementById("nip").value.trim();
  const nama = document.getElementById("nama").value.trim();
  const email = document.getElementById("email").value.trim();
  const jabatan = document.getElementById("jabatan").value.trim();
  const noTelepon = document.getElementById("no_telepon").value.trim();
  const inputFoto = document.getElementById("foto");

  const polaAngka = /^\d+$/;
  const polaEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  let pesanError = "";

  if (nip === "" || !polaAngka.test(nip)) {
    pesanError = "NIP wajib diisi dan hanya boleh berisi angka.";
  } else if (nama === "") {
    pesanError = "Nama pegawai harus diisi dan tidak boleh hanya spasi.";
  } else if (email === "" || !polaEmail.test(email)) {
    pesanError = "Email wajib diisi dengan format yang valid.";
  } else if (jabatan === "") {
    pesanError = "Silakan pilih jabatan pegawai.";
  } else if (noTelepon === "" || !polaAngka.test(noTelepon) || noTelepon.length < 12) {
    pesanError = "Nomor HP wajib diisi berupa angka minimal 12 digit.";
  } else if (inputFoto && inputFoto.files && inputFoto.files.length > 0) {
    const file = inputFoto.files[0];
    const tipeValid = ["image/jpeg", "image/png", "image/webp"];
    if (!tipeValid.includes(file.type)) {
      pesanError = "Format foto harus berupa JPG, PNG, atau WEBP.";
    } else if (file.size > 2 * 1024 * 1024) {
      pesanError = "Ukuran file foto maksimal 2 MB.";
    }
  }

  if (pesanError !== "") {
    event.preventDefault();
    tampilkanPesanValidasi(pesanError);
    return false;
  }
  return true;
}

function validasiFormLogin(event) {
  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value;

  if (username === "" || password === "") {
    event.preventDefault();
    tampilkanPesanValidasi("Username dan password wajib diisi.");
    return false;
  }
  return true;
}

function validasiFormProfil(event) {
  const username = document.getElementById("username_admin").value.trim();
  const email = document.getElementById("email_admin").value.trim();
  const jabatan = document.getElementById("jabatan_admin").value.trim();
  const inputFoto = document.getElementById("foto_admin");
  const polaEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (username === "") {
    event.preventDefault();
    tampilkanPesanValidasi("Username wajib diisi.");
    return false;
  }
  if (email === "" || !polaEmail.test(email)) {
    event.preventDefault();
    tampilkanPesanValidasi("Email administrator wajib diisi dengan format yang valid.");
    return false;
  }
  if (jabatan === "") {
    event.preventDefault();
    tampilkanPesanValidasi("Jabatan wajib diisi.");
    return false;
  }
  if (inputFoto && inputFoto.files && inputFoto.files.length > 0) {
    const file = inputFoto.files[0];
    const tipeValid = ["image/jpeg", "image/png", "image/webp"];
    if (!tipeValid.includes(file.type)) {
      event.preventDefault();
      tampilkanPesanValidasi("Format foto profil harus berupa JPG, PNG, atau WEBP.");
      return false;
    }
    if (file.size > 2 * 1024 * 1024) {
      event.preventDefault();
      tampilkanPesanValidasi("Ukuran foto profil maksimal 2 MB.");
      return false;
    }
  }
  return true;
}

function validasiFormPassword(event) {
  const passLama = document.getElementById("password_lama").value;
  const passBaru = document.getElementById("password_baru").value;
  const passKonfirmasi = document.getElementById("konfirmasi_password").value;

  if (passLama === "" || passBaru === "" || passKonfirmasi === "") {
    event.preventDefault();
    tampilkanPesanValidasi("Semua kolom password wajib diisi.");
    return false;
  }
  if (passBaru.length < 6) {
    event.preventDefault();
    tampilkanPesanValidasi("Password baru minimal 6 karakter.");
    return false;
  }
  if (passBaru !== passKonfirmasi) {
    event.preventDefault();
    tampilkanPesanValidasi("Konfirmasi password baru tidak cocok.");
    return false;
  }
  return true;
}

function tampilkanPesanValidasi(pesan) {
  const kotakAlert = document.getElementById("jsAlertBox");
  const teksAlert = document.getElementById("jsAlertText");
  if (kotakAlert && teksAlert) {
    teksAlert.textContent = pesan;
    kotakAlert.classList.remove("d-none");
    kotakAlert.scrollIntoView({ behavior: "smooth", block: "center" });
  } else {
    alert(pesan);
  }
}

$(document).ready(function () {
  const batasPerHalaman = 5;
  let halamanAktif = 1;

  // Pastikan semua tombol silang (.btn-close) pada alert langsung menutup kotak alert tanpa ngestuck
  $(document).on("click", ".alert .btn-close", function () {
    const kotak = $(this).closest(".alert");
    if (kotak.attr("id") === "jsAlertBox") {
      kotak.addClass("d-none");
    } else {
      kotak.remove();
    }
  });

  // Filter pencarian teks + filter dropdown jabatan + paginasi maks 5 baris per halaman
  function saringTabelPegawai() {
    const tabel = $("#tabelPegawai");
    if (!tabel.length) return;

    const kataKunci = ($("#cariPegawai").val() || "").toLowerCase().trim();
    const jabatanDipilih = ($("#filterJabatan").val() || "").trim();
    const barisCocok = [];

    tabel.find("tbody tr.baris-data").each(function () {
      const teksBaris = $(this).text().toLowerCase();
      const jabatanBaris = $(this).attr("data-jabatan") || "";

      const cocokTeks = kataKunci === "" || teksBaris.indexOf(kataKunci) > -1;
      const cocokJabatan = jabatanDipilih === "" || jabatanBaris === jabatanDipilih;

      if (cocokTeks && cocokJabatan) {
        barisCocok.push(this);
      } else {
        $(this).hide();
      }
    });

    const totalData = barisCocok.length;
    const totalHalaman = Math.max(1, Math.ceil(totalData / batasPerHalaman));
    if (halamanAktif > totalHalaman) {
      halamanAktif = totalHalaman;
    }

    const mulai = (halamanAktif - 1) * batasPerHalaman;
    const selesai = mulai + batasPerHalaman;

    $(barisCocok).each(function (indeks) {
      if (indeks >= mulai && indeks < selesai) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });

    $("#barisKosongFilter").toggleClass("d-none", totalData > 0);
    renderPaginasi(totalData, totalHalaman, mulai);
  }

  function renderPaginasi(totalData, totalHalaman, mulai) {
    const wadahNav = $("#wadahNavigasiHalaman");
    const wadahUl = $("#paginasiPegawai");
    const infoEl = $("#infoHalamanPegawai");
    if (!wadahUl.length) return;

    wadahUl.empty();

    if (totalData === 0) {
      wadahNav.addClass("d-none");
      return;
    }

    wadahNav.removeClass("d-none");
    const awalTampil = mulai + 1;
    const akhirTampil = Math.min(mulai + batasPerHalaman, totalData);
    infoEl.text(
      "Menampilkan " +
        awalTampil +
        "–" +
        akhirTampil +
        " dari " +
        totalData +
        " pegawai (Halaman " +
        halamanAktif +
        " / " +
        totalHalaman +
        ")"
    );

    wadahUl.append(
      '<li class="page-item ' + (halamanAktif <= 1 ? "disabled" : "") + '">' +
        '<a class="page-link" href="#" data-halaman="' + (halamanAktif - 1) + '">Sebelumnya</a>' +
      "</li>"
    );

    for (let i = 1; i <= totalHalaman; i++) {
      wadahUl.append(
        '<li class="page-item ' + (i === halamanAktif ? "active" : "") + '">' +
          '<a class="page-link" href="#" data-halaman="' + i + '">' + i + "</a>" +
        "</li>"
      );
    }

    wadahUl.append(
      '<li class="page-item ' + (halamanAktif >= totalHalaman ? "disabled" : "") + '">' +
        '<a class="page-link" href="#" data-halaman="' + (halamanAktif + 1) + '">Berikutnya</a>' +
      "</li>"
    );
  }

  $(document).on("click", "#paginasiPegawai a.page-link", function (event) {
    event.preventDefault();
    if ($(this).parent().hasClass("disabled")) return;
    const halamanBaru = parseInt($(this).attr("data-halaman"), 10);
    if (!isNaN(halamanBaru) && halamanBaru >= 1) {
      halamanAktif = halamanBaru;
      saringTabelPegawai();
    }
  });

  $("#cariPegawai").on("keyup input", function () {
    halamanAktif = 1;
    saringTabelPegawai();
  });

  $("#filterJabatan").on("change", function () {
    halamanAktif = 1;
    saringTabelPegawai();
  });

  saringTabelPegawai();

  // Modal konfirmasi hapus pegawai
  $(document).on("click", ".btn-hapus", function () {
    const idPegawai = $(this).data("id");
    const namaPegawai = $(this).data("nama");
    $("#hapusId").val(idPegawai);
    $("#hapusNama").text(namaPegawai);
  });

  // Eksekusi hapus pegawai menggunakan AJAX jQuery ($.ajax POST)
  $("#formHapusPegawai").on("submit", function (event) {
    event.preventDefault();
    const form = $(this);
    const idPegawai = $("#hapusId").val();
    const tombolSubmit = form.find('button[type="submit"]');

    tombolSubmit.prop("disabled", true);

    $.ajax({
      url: form.attr("action"),
      method: "POST",
      data: { id: idPegawai },
      dataType: "json",
      success: function (respons) {
        const modalEl = document.getElementById("modalHapus");
        const modalInst = bootstrap.Modal.getInstance(modalEl);
        if (modalInst) modalInst.hide();

        if (respons && respons.status === "sukses") {
          const baris = $('.btn-hapus[data-id="' + idPegawai + '"]').closest("tr.baris-data");
          baris.fadeOut(200, function () {
            $(this).remove();
            const sisaBaris = $("#tabelPegawai tbody tr.baris-data").length;
            $("#totalPegawaiCount").text(sisaBaris);
            saringTabelPegawai();
          });
          tampilkanAlertAjax("success", respons.pesan || "Data pegawai berhasil dihapus.");
        } else {
          tampilkanAlertAjax("danger", (respons && respons.pesan) || "Gagal menghapus data pegawai.");
        }
      },
      error: function (xhr) {
        const modalEl = document.getElementById("modalHapus");
        const modalInst = bootstrap.Modal.getInstance(modalEl);
        if (modalInst) modalInst.hide();

        const pesan =
          xhr.responseJSON && xhr.responseJSON.pesan
            ? xhr.responseJSON.pesan
            : "Terjadi kesalahan saat menghubungi server.";
        tampilkanAlertAjax("danger", pesan);
      },
      complete: function () {
        tombolSubmit.prop("disabled", false);
      },
    });
  });

  function tampilkanAlertAjax(tipe, pesan) {
    const ikon = tipe === "success" ? "bi-check-circle-fill" : "bi-exclamation-triangle-fill";
    const alertEl = $(
      '<div class="alert alert-' +
        tipe +
        ' alert-dismissible fade show d-flex align-items-center justify-content-between" role="alert">' +
        '<div class="d-flex align-items-center">' +
          '<i class="bi ' +
          ikon +
          ' me-2"></i>' +
          '<div class="pesan-teks"></div>' +
        "</div>" +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup" onclick="this.closest(\'.alert\').remove()"></button>' +
      "</div>"
    );
    alertEl.find(".pesan-teks").text(pesan);
    $("#ajaxAlertContainer").empty().append(alertEl);
  }

  // Pratinjau foto saat dipilih di form pegawai maupun form profil admin
  $("#foto, #foto_admin").on("change", function () {
    const file = this.files && this.files[0];
    if (!file) return;
    const targetImg = this.id === "foto_admin" ? "#previewFotoAdmin" : "#previewFoto";
    const reader = new FileReader();
    reader.onload = function (e) {
      $(targetImg).attr("src", e.target.result).removeClass("d-none");
      if (targetImg === "#previewFotoAdmin") {
        $("#avatarIconAdmin").addClass("d-none");
      }
    };
    reader.readAsDataURL(file);
  });
});
