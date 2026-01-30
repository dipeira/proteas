<?php
header('Content-type: text/html; charset=utf-8');
require_once "../config.php";
require_once "../include/functions.php";
require "../tools/class.login.php";

$log = new logmein();
if ($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
    exit;
}

// Fetch comments
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
$sxol_etos = getParam('sxol_etos', $conn);

$query = "
    SELECT sc.*, s.name AS school_name, s.code AS school_code, l.username AS added_by_username
    FROM school_comments sc
    LEFT JOIN school s ON sc.school_id = s.id
    LEFT JOIN logon l ON sc.added_by = l.userid
    WHERE sxol_etos = $sxol_etos
    ORDER BY sc.added_at DESC, sc.id DESC
";
$result = mysqli_query($conn, $query);
?>
<html>
<head>
<?php
  $root_path = '../';
  $page_title = 'Σχόλια / ενέργειες σχολικών μονάδων';
  require '../etc/head.php';
?>
<link href="../css/style.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/jquery-ui.min.js"></script>
<?php require_once('../js/datatables/includes.html'); ?>
<style>
  body { padding: 20px; }
  .page-container { max-width: 1400px; margin: 0 auto; }
  .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
  .page-header h2 { margin: 0; }
  #comments-table tbody tr { cursor: pointer; }
  #comments-table td, #comments-table th { white-space: nowrap; }
  #comments-table td.comment-cell, #comments-table td.action-cell { white-space: normal; max-width: 320px; }
  .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 0.82rem; }
  .badge.success { background: #d1fae5; color: #065f46; }
  .badge.muted { background: #f3f4f6; color: #374151; }
</style>
<script type="text/javascript">
$(document).ready(function() {
  const table = $('#comments-table').DataTable({
    pageLength: 25,
    order: [[4, 'desc']],
    language: {
      url: '../js/datatables/Greek.json'
    }
  });

  $('#status-filter').on('change', function() {
    const value = $(this).val();
    table.column(6).search(value).draw();
  });

  const $modal = $("#comment-modal").dialog({
    autoOpen: false,
    modal: true,
    width: 650,
    buttons: [{
      text: "Κλείσιμο",
      click: function() { $(this).dialog("close"); }
    }]
  });

  $('#comments-table tbody').on('click', 'tr', function() {
    const data = table.row(this).data();
    const $row = $(this);
    $('#modal-school').text($row.data('school'));
    $('#modal-comment').text($row.data('comment'));
    $('#modal-action').text($row.data('action'));
    $('#modal-added-by').text($row.data('added-by'));
    $('#modal-added-at').text($row.data('added-at'));
    $('#modal-done').text($row.data('done'));
    $('#modal-done-at').text($row.data('done-at'));
    $('#modal-updated-at').text($row.data('updated-at'));
    $modal.dialog('open');
  });
});
</script>
</head>
<body>
  <?php require '../etc/menu.php'; ?>
  <div class="page-container">
    <div class="page-header">
      <h2>Σχόλια / ενέργειες σχολικών μονάδων</h2>
      <div>
        <button onclick="window.location.reload();" class="btn">Ανανέωση</button>
      </div>
    </div>
    <div class="filter-container" style="margin-bottom: 16px;">
      <label for="status-filter">Φίλτρο κατάστασης:</label>
      <select id="status-filter">
        <option value="">Όλα</option>
        <option value="ΝΑΙ">Ολοκληρωμένα</option>
        <option value="ΟΧΙ">Εκκρεμή</option>
      </select>
    </div>
    <table id="comments-table" class="display">
      <thead>
        <tr>
          <th>ID</th>
          <th>Σχολείο</th>
          <th>Σχόλιο</th>
          <th>Ενέργεια</th>
          <th>Υποβλήθηκε</th>
          <th>Καταχώρησε</th>
          <th>Ολοκλήρωση</th>
          <th>Ενημερώθηκε</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): 
          $schoolLabel = trim(($row['school_code'] ? $row['school_code'].' - ' : '') . ($row['school_name'] ?? ''));
          $addedBy = $row['added_by_username'] ? $row['added_by_username'] : '—';
          $done = (int)$row['done'] === 1;
          $doneLabel = $done ? 'ΝΑΙ' : 'ΟΧΙ';
          $doneAt = $row['done_at'] !== '0000-00-00' ? date('d-m-Y', strtotime($row['done_at'])) : '—';
          $addedAt = $row['added_at'] ? date('d-m-Y H:i', strtotime($row['added_at'])) : '—';
          $updatedAt = $row['updated'] ? date('d-m-Y H:i', strtotime($row['updated'])) : '—';
          $comment = htmlspecialchars($row['comment']);
          $action = htmlspecialchars($row['action']);
        ?>
        <tr
          data-school="<?php echo htmlspecialchars($schoolLabel); ?>"
          data-school-id="<?php echo (int)$row['school_id']; ?>"
          data-comment="<?php echo $comment; ?>"
          data-action="<?php echo $action; ?>"
          data-added-by="<?php echo htmlspecialchars($addedBy); ?>"
          data-added-at="<?php echo $addedAt; ?>"
          data-done="<?php echo $doneLabel; ?>"
          data-done-at="<?php echo $doneAt; ?>"
          data-updated-at="<?php echo $updatedAt; ?>"
        >
          <td><?php echo (int)$row['id']; ?></td>
          <td><a href="../school/school_status.php?org=<?php echo (int)$row['school_id']; ?>" ><?php echo htmlspecialchars($schoolLabel); ?></a></td>
          <td class="comment-cell"><?php echo shorten_text($comment,100); ?></td>
          <td class="action-cell"><?php echo shorten_text($action,100); ?></td>
          <td><?php echo $addedAt; ?></td>
          <td><?php echo htmlspecialchars($addedBy); ?></td>
          <td><?php echo $done ? "<span class='badge success'>ΝΑΙ</span>" : "<span class='badge muted'>ΟΧΙ</span>"; ?></td>
          <td><?php echo $updatedAt; ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div id="comment-modal" title="Λεπτομέρειες σχολίου" style="display:none;">
    <div class="info-grid" style="display:grid;grid-template-columns: 140px 1fr;grid-row-gap:8px;grid-column-gap:12px;">
      <strong>Σχολείο:</strong> <span id="modal-school"></span>
      <strong>Σχόλιο:</strong> <span id="modal-comment"></span>
      <strong>Ενέργεια:</strong> <span id="modal-action"></span>
      <strong>Καταχώρησε:</strong> <span id="modal-added-by"></span>
      <strong>Υποβλήθηκε:</strong> <span id="modal-added-at"></span>
      <strong>Ολοκλήρωση:</strong> <span id="modal-done"></span>
      <strong>Ημ/νία ολοκλήρωσης:</strong> <span id="modal-done-at"></span>
      <strong>Τελ.ενημέρωση:</strong> <span id="modal-updated-at"></span>
    </div>
  </div>
</body>
</html>

