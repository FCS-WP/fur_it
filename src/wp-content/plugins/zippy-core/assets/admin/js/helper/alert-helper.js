import Swal from "sweetalert2";

export const AlertStatus = {
  error: "error",
  success: "success",
  warning: "warning",
};

export const showAlert = async (status, title, text, timer = 3000) => {
  Swal.fire({
    icon: status,
    title,
    text,
    timer: 3000,
    showConfirmButton: false,
  });
};

export const bookingSuccessfully = (handleConfirm) => {
  Swal.fire({
    customClass: "booking_success",
    title: "Booking Successful",
    text: "Your booking has been created successfully!",
    icon: "success",
    showCancelButton: true,
    confirmButtonText: "View Booking",
    cancelButtonText: "Cancel",
    timer: 0,
  }).then((result) => {
    handleConfirm(result);
  });
};

export const alertInputEmail = async () => {
  const { value: email } = await Swal.fire({
    title: "EMAIL ADDRESS ",
    input: "email",
    inputPlaceholder: "Enter your email address",
    showCancelButton: true,
    confirmButtonText: "Continue",
    focusConfirm: true,
    customClass: {
      input: "swal-input-email",
      confirmButton: "swal-confirm-btn",
    },
    inputValidator: (email) => {
      const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      if (!email || !regex.test(email)) {
        return "Please enter a valid email address!";
      }
    },
  });
  if (email) {
    return email;
  }
  return null;
};

export const alertConfirmDelete = async () => {
  const confirmed = Swal.fire({
    title: "Are you sure?",
    text: "You won't be able to revert this!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then((result) => {
    if (result.isConfirmed) {
      return true;
    } else {
      return false;
    }
  });
  return confirmed;
};

export const alertConfirm = (
  title,
  desc,
  confirmText,
  showCancelBtn = false
) => {
  const confirmed = Swal.fire({
    title: title,
    text: desc,
    icon: "warning",
    showCancelButton: showCancelBtn,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: confirmText,
  }).then((result) => {
    if (result.isConfirmed) {
      return true;
    } else {
      return false;
    }
  });
  return confirmed;
};

export const inputAjaxAlert = (
  item_id,
  template_src,
  order_id,
  phone_number = "84384375658"
) => {
  Swal.fire({
    title: "Add your comment:",
    input: "textarea",
    inputAttributes: {
      autocapitalize: "off",
    },
    showCancelButton: true,
    confirmButtonText: "Send request",
    showLoaderOnConfirm: true,
    preConfirm: async (userComments) => {
      if (!userComments.trim()) {
        Swal.showValidationMessage("Please enter your comments.");
        return false;
      }
      return userComments;
    },
    allowOutsideClick: () => !Swal.isLoading(),
  }).then((result) => {
    if (result.isConfirmed && result.value) {
      const userComment = result.value;
      const message = `Hi Mach Photo,
I would like to update the photobook template ${template_src} (order #${order_id}) based on the following new comments/feedback:

${userComment}`;

      const whatsappUrl = `https://wa.me/${phone_number}?text=${encodeURIComponent(
        message
      )}`;
      window.open(whatsappUrl, "_blank");
    }
  });
};
