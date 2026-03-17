export const downloadBase64File = (base64, fileName, fileType) => {
  if (!base64) {
    console.error("No file data provided");
    // alert("The file is empty. Nothing to download.");
    return;
  }

  try {
    const byteCharacters = atob(base64);
    const byteNumbers = new Array(byteCharacters.length);
    for (let i = 0; i < byteCharacters.length; i++) {
      byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);

    let mimeType = "application/octet-stream";
    if (fileType === "csv") mimeType = "text/csv;charset=utf-8;";
    if (fileType === "pdf") mimeType = "application/pdf";

    const blob = new Blob([byteArray], { type: mimeType });

    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = fileName || `download.${fileType}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error("Download failed:", error);
  }
};
