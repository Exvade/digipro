document.addEventListener("DOMContentLoaded", function () {
    let swalStatus = document.querySelector("meta[name='swal-status']");
    let swalMessage = document.querySelector("meta[name='swal-message']");

    if (swalStatus && swalMessage) {
        let status = swalStatus.content;
        let message = swalMessage.content;

        let icon = "success";
        let title = "Berhasil!";
        let showClass = { popup: "animate__animated animate__fadeIn" };
        let hideClass = { popup: "animate__animated animate__fadeOut" };

        switch (status) {
            case "created":
                title = "Data berhasil ditambahkan";
                icon = "success";
                break;
            case "updated":
                title = "Data berhasil diubah";
                icon = "info";
                break;
            case "deleted":
                title = "Data berhasil dihapus";
                icon = "error";
                break;
        }

        Swal.fire({
            icon: icon,
            title: title,
            text: message,
            timer: 2000,
            showConfirmButton: false,
            showClass: showClass,
            hideClass: hideClass,
            background: "#ffffff",
            backdrop: true
        });
    }

    // Konfirmasi sebelum hapus
    let deleteForms = document.querySelectorAll("form.delete-form");
    deleteForms.forEach((form) => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data yang dihapus tidak dapat dikembalikan.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus",
                cancelButtonText: "Batal",
                showClass: { popup: "animate__animated animate__fadeIn" },
                hideClass: { popup: "animate__animated animate__fadeOut" }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
