document.addEventListener('copy', (event) => {
    const pagelink = `\n\nRead more at: ${document.location.href}`;
    event.clipboardData.setData('text', document.getSelection() + pagelink);
    event.preventDefault();
  });