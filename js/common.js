function confirmDelete(delUrl) {
  if (confirm("ΠΡΟΣΟΧΗ - Είστε σίγουροι ότι θέλετε να διαγράψετε αυτήν την εγγραφή;")) {
    document.location = delUrl;
  }
}
