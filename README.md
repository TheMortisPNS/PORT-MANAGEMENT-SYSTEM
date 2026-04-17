# 🚢 ATLAS GROUP - Port Management System

Σύστημα Διαχείρισης Λιμένα | Πανεπιστήμιο Δυτικής Αττικής  
Μάθημα: Τεχνολογίες Διαδικτύου στη Ψηφιακή Βιομηχανία

## 📋 Περιγραφή
Web εφαρμογή διαχείρισης αφίξεων και αναχωρήσεων πλοίων σε λιμάνι.  
Αναπτύχθηκε με PHP, MySQL και Bootstrap 5.

## 🛠️ Τεχνολογίες
- PHP 8.x
- MySQL / MariaDB
- Bootstrap 5.3
- HTML5 / CSS3 / JavaScript
- XAMPP (Apache)

## 📂 Αρχεία
| Αρχείο | Περιγραφή |
|--------|-----------|
| `index.php` | Αρχική σελίδα - Λίστα αφίξεων |
| `add_ship.php` | Φόρμα καταχώρησης νέου πλοίου |
| `insert.php` | Επεξεργασία δεδομένων φόρμας |
| `edit_ship.php` | Επεξεργασία στοιχείων πλοίου |
| `delete.php` | Διαγραφή εγγραφής |
| `search.php` | Αναζήτηση πλοίων |
| `port_status.php` | Real-time Port Status Board |
| `calendar.php` | Ημερολόγιο αφίξεων |
| `statistics.php` | Στατιστικά λιμανιού |
| `db_connect.php` | Σύνδεση με βάση δεδομένων |
| `port_db.sql` | Εξαγωγή βάσης δεδομένων |

## 🗄️ Εγκατάσταση
1. Εγκατάσταση XAMPP
2. Αντιγραφή φακέλου στο `C:/xampp/htdocs/shipping_port`
3. Εισαγωγή `port_db.sql` στο phpMyAdmin
4. Εκκίνηση Apache & MySQL
5. Άνοιγμα `http://localhost/shipping_port/index.php`

## 🤖 Χρήση AI
Χρησιμοποιήθηκε το **Abacus AI ChatLLM** για:
- Σχεδιασμό δομής βάσης δεδομένων
- Δημιουργία PHP κώδικα (CRUD operations)
- Σχεδιασμό UI/UX με CSS
- Debugging και επίλυση σφαλμάτων

Τμήματα κώδικα που δημιουργήθηκαν με AI:
- `port_status.php` (Port Status Board - modal system)
- `statistics.php` (γραφήματα)
- CSS styling (navbar, cards, modal)
