<a href="<?=$site_root?>"><IMG src="<?=$site_root?>/images/logo.png" class="applogo"></a>
<div id="main-menu">
		<ul id="navigation">
			<li><a href="<?=$site_root?>/index.php">Αρχικη</a></li>
			
      <li class="sub">
        <a href="<?=$site_root?>/index.php">Μονιμοι</a>
				<ul>
          <li><a href="<?=$site_root?>/index.php">Λίστα</a></li>
					<li><a href="<?=$site_root?>/employee/idiwtikoi.php">Ιδιωτικοί εκπ/κοί</a></li>
					<li><a href="<?=$site_root?>/employee/apof_ad.php">Αποφάσεις Αδειών</a></li>
				</ul>
			</li>

      <li class="sub">
				<a href="<?=$site_root?>/employee/ektaktoi_list.php">Αναπληρωτες</a>
				<ul>
					<li><a href="<?=$site_root?>/employee/ektaktoi_list.php">Λίστα</a></li>
          <li><a href="<?=$site_root?>/employee/praxi.php">Διαχείριση πράξεων</a></li>
		  		<li><a href="<?=$site_root?>/tools/assign_praxi.php">Ανάθεση πράξεων</a></li>
          <li><a href="<?=$site_root?>/employee/praxi_sch.php">Εκπαιδευτικοί & Σχολεία ανά Πράξη</a></li>
					<li><a href="<?=$site_root?>/employee/ektaktoi_prev.php">Προηγούμενου έτους</a></li>
          <li>&nbsp;&nbsp;- - - - - - - - - - - - -</li>
          <li><a href="<?=$site_root?>/tools/ektaktoi_import.php">Μαζική εισαγωγή</a></li>
					<li><a href="<?=$site_root?>/tools/ektaktoi_import_minedu.php">Μαζική εισαγωγή από αρχεία Υπουργείου (beta)</a></li>
          <li><a href="<?=$site_root?>/employee/ektaktoi_top.php">Μαζική τοποθέτηση</a></li>
				</ul>
			</li>

      <li class="sub">
				<a href="<?=$site_root?>/school/school.php">Σχολειο</a>
				<ul>
					<li><a href="<?=$site_root?>/school/school.php">Λίστα</a></li>
					<li><a href="<?=$site_root?>/school/school_status.php">Καρτέλα</a></li>
					<li><a href="<?=$site_root?>/school/school_edit.php">Επεξεργασία</a></li>
				</ul>
			</li>
		 
		 	<li class="sub">
		  	<a href="#">Αναφορες</a>
    		<ul>
		   		<li><a href="<?=$site_root?>/reports/report_tm_ekp.php">Μαθητές & Εκπ/κοί</a></li>
           <li><a href="<?=$site_root?>/reports/report_head.php">Διευθυντές / Προϊστάμενοι</a></li>
					<li><a href="<?=$site_root?>/reports/report_kena.php?type=1">Οργανικά Κενά</a></li>
					<li><a href="<?=$site_root?>/reports/report_leit.php">Λειτουργικά Κενά Δημοτικών</a></li>
					<li><a href="<?=$site_root?>/reports/report_tm_ekp.php?type=4">Λειτουργικά Κενά Νηπιαγωγείων</a></li>
					<li><a href="<?=$site_root?>/reports/report_leit_yp.php">Λειτουργικά Κενά (για Υπουργείο)</a></li>
					<!--<li><a href="report_leit.php">Λειτουργικά Κενά (από υπολογισμό)</a></li>-->
					<!--<li><a href="report_kena_eid.php?klados=3">Λειτουργ.Κενά ΠΕ06 & ΠΕ11</a></li>-->
					<!--<li><a href="report_kena_eid_eaep.php?klados=3">Λειτουργ.Κενά Ειδικοτήτων ΕΑΕΠ</a></li>-->
					<li><a href="<?=$site_root?>/reports/report_symv.php">Στατιστικά συμβούλων εκπαίδευσης</a></li>
					<li><a href="<?=$site_root?>/employee/absents.php">Εκπ/κοί σε άδεια</a></li>
					<li><a href="<?=$site_root?>/etc/check_wres.php">Συμπλήρωση υποχρ.ωραρίου</a></li>
					<li><a href="<?=$site_root?>/employee/simplirosi.php">Συμπλήρωση υποχρ.ωραρίου ανά κλάδο & σχολεία</a></li>
          <li><a href="<?=$site_root?>/etc/stats.php">Στατιστικά</a></li>
		  	</ul>
		 	</li>
                 
      <li class="sub">
		  	<a href="#">Αναζητηση</a>
      	<ul>
					<li><a href="<?=$site_root?>/employee/search.php">Προσωπικού</a></li>
					<li><a href="<?=$site_root?>/employee/search_adeia.php">Αδειών</a></li>
		  	</ul>
		 	</li>
		 <?php
		 if ($_SESSION['userlevel'] == 0):
		 ?>
		 <li class="sub">
		  <a href="#">Διαχειριση</a>
		  <ul>
			  <li><a href="<?=$site_root?>/etc/end_of_year.php">Λήξη Διδακτικού Έτους - Ενέργειες</a></li>
			  <li><a href="<?=$site_root?>/employee/check_vmk.php">Αλλαγές ΜΚ</a></li>
        <li><a href='<?=$site_root?>/tools/import.php'>Εισαγωγή δεδομένων</a></li>
			  <li><a href='<?=$site_root?>/etc/params.php'>Παράμετροι</a></li>
			  <li><a href="<?=$site_root?>/employee/klados.php">Ειδικότητες</a></li>
			  <li><a href='<?=$site_root?>/etc/users.php'>Διαχείριση Χρηστών</a></li>
				<li><a href='<?=$site_root?>/employee/symvouloi.php'>Ενότητες συμβούλων</a></li>
			  <li><a href='<?=$site_root?>/etc/log.php'>Αρχείο καταγραφής συμβάντων</a></li>
        <li><a href='<?=$site_root?>/school/school_log.php'>Αρχείο καταγραφής πρόσβασης σχολείων</a></li>
				<li><a href='<?=$site_root?>/school/requests.php'>Αιτήματα Σχολείων</a></li>
        <li><a href='<?=$site_root?>/tools/fix_leitoyrg.php'>Επιδιόρθωση λειτουργικότητας</a></li>
		  </ul>
		 </li>
     <?php elseif($_SESSION['requests']): ?>
		 <li class="sub">
		  <a href="#">Διαχειριση</a>
		  <ul>
				<li><a href='<?=$site_root?>/school/requests.php'>Αιτήματα Σχολείων</a></li>
		  </ul>
		 </li>
		 <?php endif; ?>
		 <li class="sub">
		  <a href="<?=$site_root?>/etc/about.php">Σχετικα</a>
		 </li>
		<li class="sub">
			<a href='<?=$site_root?>/tools/login.php?logout=1'>Eξοδος</a>
		</ul>
  
	</div>