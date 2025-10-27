<!-- Esercizio didattico completo in PHP + HTML + un po’ di JavaScript che mostra: le differenze pratiche tra GET e POST la validazione lato client (FE) con JavaScript / HTML5 la validazione lato server (BE) in PHP la sanificazione / protezione contro attacchi comuni (XSS, valori malevoli).

Descrizione dell’esercizio
Facciamo un semplice form di “contatto” con questi campi:
Nome (obbligatorio, solo lettere / spazi)
Email (obbligatorio, formato valido)
Messaggio (opzionale, al massimo 300 caratteri)


Useremo method GET per la parte dimostrativa e poi modificheremo a POST per i dati “sensibili”.
Mostreremo gli errori se ci sono problemi e, in caso di successo, visualizzeremo un messaggio “Grazie, i tuoi dati sono stati inviati”.

Struttura file
Puoi fare un unico file contact.php che contiene sia il form HTML sia la logica PHP in cima.

Spiegazion
Differenza GET vs POST
Con method="GET", i dati del form vengono inviati nella query string dell’URL (es. contact.php?name=Maria&email=...). Questo significa che:

• I dati sono visibili nell’URL → non sicuro per dati sensibili Si possono fare bookmark / condivisione del link con i parametri
• C’è un limite pratico alla lunghezza dell’URL
• È utile per operazioni “idempotenti” come una ricerca

Con method="POST", i dati sono inviati nel body della richiesta HTTP:

• Non visibili nell’URL
• Non c’è il limite stringente di lunghezza (o comunque è molto maggiore)
• Più adatto per login, invio dati sensibili, inserimenti su DB


Validazione lato client (JavaScript / HTML5)
Serve a dare feedback all’utente immediatamente, prima che il form venga inviato
Non è affidabile da solo: l’utente può disabilitare JavaScript o manipolare la richiesta
Qui uso onsubmit="return validateFormJS()" per bloccare l’invio se ci sono errori
Inoltre puoi usare attributi HTML5 come required, type="email", maxlength, pattern per aiutare il browser a validare

Validazione lato server (PHP)
Obbligatoria: anche se il client validava, i dati arrivano comunque al server — serve verificare nuovamente
Uso $_REQUEST (o $_GET / $_POST) secondo il metodo
Uso sanitize_input(...) per pulire il dato da spazi, backslash, caratteri speciali → ridurre rischio di XSS
Uso preg_match per validare il nome
Uso filter_var(..., FILTER_VALIDATE_EMAIL) per verificare che l’email sia ben formata
Controllo lunghezza per il messaggio
Se tutto è ok, posso “processare” (qui semplicemente visualizzo un messaggio)


Sicurezza / protezione
Uso htmlspecialchars($_SERVER["PHP_SELF"]) per evitare che l’attributo action del form venga “iniettato” con codice malevolo (principio anti-XSS)
Sanificazione degli input prima dell’uso
Validazione rigorosa per non accettare dati non previsti


Possibili estensioni / varianti
Cambiare $method = "POST" per versione “reale”
Aggiungere campi come “numero di telefono”, “sito web”, “checkbox” (accetto termini)
Usare pattern HTML5 (pattern="^…$") per validazione minima nel browser
Dopo invio con successo, fare header("Location: thankyou.php") + exit; per evitare doppio invio (post/redirect/get)
Usare librerie PHP per validazione più potente (es. Symfony Validator, Respect/Validation) -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        input::placeholder, textarea::placeholder {
            font-style: italic;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <?php
    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST)) {
        function sanitize_input($data, $key, ) {
            if ($key === "nome") {
                if (preg_match("/^[A-Za-z\s]+$/", $data) === 0) {
                    print_r("Nome non valido. Usa solo lettere e spazi.<br><br><br>");
                    return "";
                }
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
            }
            
            else if ($key === "email") {
                if (filter_var($data, FILTER_VALIDATE_EMAIL) === false) {
                    print_r("Email non valida.<br><br><br>");
                    return "";
                }
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
            }
            
            else if ($key === "notes") {
                if (strlen($data) > 300) {
                    print_r("Il messaggio non può superare i 300 caratteri.<br><br><br>");
                    return "";
                }
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
            }
            return $data;
        }
        $nome = isset($_POST["name"]) ? sanitize_input($_POST["name"], "nome") : "";
        $email = isset($_POST["email"]) ? sanitize_input($_POST["email"], "email") : "";
        $notes = isset($_POST["notes"]) ? sanitize_input($_POST["notes"], "notes") : "";

        if ($nome !== "" && $email !== "" && $notes !== "") {
            print_r("Grazie, i tuoi dati sono stati inviati.<br><br><br>");
        }
        
    }
    
    ?>
    <form action="" method="POST" target="_blank" onsubmit="return validateFormJS()">
        <label for="name">Nome: </label><br>
        <input type="text" id="name" name="name" required pattern="[A-Za-z\s]+" title="Solo lettere e spazi" placeholder="Mario Rossi"><br><br>
        
        <label for="email">Email: </label><br>
        <input type="text" id="email" name="email" required placeholder="email@example.com" title="Inserire una mail valida"><br><br>

        <label for="notes">Note: </label><br>
        <textarea name="notes" id="notes" maxlength="300" placeholder="Messaggio..."></textarea>
        <div id="the-count">
            <span id="current">0</span>
            <span id="maximum">/ 300</span>
        </div><br><br>

        <input type="submit" value="Invia">

    </form>
    
    <script>
        let textarea = document.getElementById('notes');
        textarea.addEventListener("keyup", () => {
            let characterCount = textarea.value.length ,
                current = document.getElementById('current'),
                maximum = document.getElementById('maximum'),
                theCount = document.getElementById('the-count');
                
            current.innerText = characterCount;
        });

        function validateFormJS() {
            let name = document.getElementById('name').value;
            let email = document.getElementById('email').value;
            let notes = document.getElementById('notes').value;

            let namePattern = /^[A-Za-z\s]+$/;
            if (!namePattern.test(name)) {
                alert("Nome non valido. Usa solo lettere e spazi.");
                return false;
            }

            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert("Email non valida.");
                return false;
            }

            if (notes.length > 300) {
                alert("Il messaggio non può superare i 300 caratteri.");
                return false;
            }
            return true; 
        }
    </script>                                                   
</body>
</html>

