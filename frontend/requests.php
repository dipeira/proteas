<?php
function requests_table($endpoint, $token, $sch) {
  $data = fetchDataFromAPI($endpoint, ['action' => 'list', 'school_code' => $sch], $token);

  if ($data){
    echo "<h4>Αιτήματα σχολείου</h4>";
    echo "<table id=\"mytbl4\" class='table table-sm table-striped' border=\"2\">\n";
    echo "<thead><th>Αίτημα</th><th>Ημ/νία υποβολής</th><th>Διεκπεραιώθηκε</th><th>Σχόλιο Δ/νσης</th><th>Ημ/νία Διεκπ.</th>";
    echo "</thead><tbody>";
    foreach ($data as $row) {
      echo "<tr>";
      echo "<td>".$row['request']."</td>"."<td>".date("d/m/Y, H:i:s", strtotime($row['submitted']))."</td>";
      echo $row['done'] ? "<td>Ναι</td>" : "<td>Όχι</td>";
      echo "<td>".$row['comment']."</td>"."<td>";
      echo strtotime($row['handled']) ? date("d/m/Y, H:i:s", strtotime($row['handled']))."</td>" : '</td>';
      echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
  } else {
    echo "<p>Δεν έχουν καταχωρηθεί αιτήματα</p>";
  }
}

function displayAddModal($sch) {
  echo <<<HTML
  <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="editModalLabel">Υποβολή αιτήματος σχολείου</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>
              </div>
              <div class="modal-body">
                  <!-- Form for editing the record -->
                  <form id="addRecordForm">
                      <!-- Add form fields for editing record details -->
                      <div class="mb-3">
                          <label for="editRequest">Αίτημα</label>
                          <textarea class="form-control" id="editRequest" rows="4"></textarea>
                      </div>
                      <input type="hidden" id="schId" name="schId" value="{$sch}">
                      <!-- Include other fields for editing -->
                      <button type="submit" class="btn btn-primary">Υποβολή αιτήματος</button>
                  </form>
              </div>
          </div>
      </div>
  </div>
HTML;
}
?>