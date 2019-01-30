function confirmDelete(delUrl) {
  if (confirm("ΠΡΟΣΟΧΗ - Είστε σίγουρος ότι θέλετε να διαγράψετε την εγγραφή;")) {
    document.location = delUrl;
  }
}
