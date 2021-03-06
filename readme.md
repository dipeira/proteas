# Πρωτέας

Το project Πρωτέας σχεδιάζεται και αναπτύσσεται από τον εκπαιδευτικό Πληροφορικής Δ.Ε. ΠΕ86 Βαγγέλη Ζαχαριουδάκη από το σχολικό έτος 2011-2012 έως σήμερα, για λογαριασμό της Δ/νσης Πρωτοβάθμιας Εκπαίδευσης Ν.Ηρακλείου.

Αποτελεί μία ολοκληρωμένη βάση δεδομένων υπαλλήλων Πρωτοβάθμιας εκπαίδευσης.

### Χαρακτηριστικά:

- Καρτέλα μόνιμου/αναπληρωτή εκπ/κού (στοιχεία, υπηρετήσεις, άδειες, ιδιωτικά έργα, χρόνοι υπηρεσίας κλπ.)
- Υπολογισμός λειτουργικών/οργανικών κενών/πλεονασμάτων
- Δημιουργία αναφορών σχολικών μονάδων
- Δημιουργία βεβαιώσεων υπηρεσίας αναπληρωτών & μόνιμων εκπ/κών
- ~~Μισθολογική ωρίμανση εκπ/κών~~

### Απαιτήσεις:
Α) Για κανονική εγκατάσταση, υπολογιστής με web server (Apache2 / nginx), PHP (προτιμότερα 7.2 - θα διορθωθεί σύντομα και για μεγαλύτερη έκδοση) & MySQL (προτιμότερα 5.7).

Β) Για εγκατάσταση με docker, αρκεί να είναι εγκατεστημένα τα [Docker](https://docs.docker.com/get-docker/) & του [Docker-compose](https://docs.docker.com/compose/install/)

### Οδηγίες εγκατάστασης:
- Κλωνοποίηση του repository σε φάκελο του web server, για άμεση λήψη ενημερώσεων (με git pull). Εναλλακτικά, αποσυμπίεση αρχείου zip σε φάκελο του web server
- Εγκατάσταση απαιτούμενων βιβλιοθηκών με την εντολή `composer update` (σε server με εγκατεστημένο τον [composer](https://getcomposer.org/))
- Δημιουργία του config.php (με βάση το config-sample.php) και εισαγωγή των στοιχείων της βάσης δεδομένων
- Εκτέλεση του init.php (http://servername/proteas/init.php) για αρχικοποίηση της βάσης δεδομένων (να διαγραφεί αφού ολοκληρωθεί η εργασία)
- Είσοδος στο σύστημα με username/password: admin / admin
- Εισαγωγή στοιχείων υπαλλήλων & σχολείων μέσω του αντίστοιχου εργαλείου εισαγωγής (από το μενού διαχείρισης)
- Ρύθμιση παραμέτρων συστήματος (από μενού διαχείρισης)
- (Προαιρετικά) Προσαρμογή προτύπων αρχείων word (μέσα στο φάκελο word)

### Οδηγίες εγκατάστασης με docker:
- Εγκατάσταση του [Docker](https://docs.docker.com/get-docker/) & του [Docker-compose](https://docs.docker.com/compose/install/)
- Κλωνοποίηση του repository σε φάκελο του web server, για άμεση λήψη ενημερώσεων (με git pull). Εναλλακτικά, αποσυμπίεση αρχείου zip σε φάκελο του web server
- Εκτέλεση της εντολής `docker-compose build` για προετοιμασία των σχετικών images (απαιτεί αρκετό χρόνο, ανάλογα με την ταχύτητα σύνδεσης & του server)
- Δημιουργία του config.php (με βάση το config-sample.php) και εισαγωγή των στοιχείων της βάσης δεδομένων, τα οποία πρέπει να συμφωνούν με αυτά που βρίσκονται στο `docker-compose.yml`
- Εκτέλεση της εντολής `docker-compose -d up` για εκκίνηση της εφαρμογής
- Εκτέλεση της εντολής `docker-compose exec app composer update` για λήψη των απαιτούμενων βιβλιοθηκών
- Εκτέλεση του init.php (http://servername/init.php) για αρχικοποίηση της βάσης δεδομένων (να διαγραφεί αφού ολοκληρωθεί η εργασία)
- Είσοδος στο σύστημα με username/password: admin / admin
- Εισαγωγή στοιχείων υπαλλήλων & σχολείων μέσω του αντίστοιχου εργαλείου εισαγωγής (από το μενού διαχείρισης)
- Ρύθμιση παραμέτρων συστήματος (από μενού διαχείρισης)
- (Προαιρετικά) Προσαρμογή προτύπων αρχείων word (μέσα στο φάκελο word)

### Βοήθεια
Η τεκμηρίωση του Πρωτέα είναι υπό κατασκευή. 

Mπορείτε να λάβετε βοήθεια: 
- ανοίγοντας issue στο GitHub. 
- στέλνοντας email στο: it@dipe.ira.sch.gr. 

Τέλος, τα pull requests είναι πάντα ευπρόσδεκτα για τη δική σας συνεισφορά στο project!

### Εργαλεία:
Η ανάπτυξη γίνεται εξ'ολοκλήρου με λογισμικό ανοιχτού κώδικα (open-source).
Τα εργαλεία που χρησιμοποιούνται είναι η γλώσσα PHP, η βάση δεδομένων MySQL και ο web server Apache.

Επίσης χρησιμοποιούνται:

- [jQuery](https://jquery.com/)
- [DataTables](https://datatables.net/)
- [PHP Calandar Date Picker](http://www.triconsole.com/php/calendar_datepicker.php)
- [Select2](https://select2.org/)
- [StickyTableHeaders](https://github.com/jmosbech/StickyTableHeaders)
- [DrasticGrid](https://www.linux.com/news/quickly-put-data-mysql-web-drasticgrid)
- [PHPExcel](https://github.com/PHPOffice/PHPExcel)
- [PHPWord](https://github.com/PHPOffice/PHPWord)
- [SwiftMailer](https://swiftmailer.symfony.com/)
- [Simple PHP/MySQL authentication/login class](http://www.emirplicanic.com/php/simple-phpmysql-authentication-class)
- [Paginator](https://code.tutsplus.com/tutorials/how-to-paginate-data-with-php--net-2928)

Το σύστημα βρίσκεται υπό συνεχή ανάπτυξη, ανάλογα με τις ανάγκες της Δ/νσης Π.Ε. Ηρακλείου.



(c) 2021
