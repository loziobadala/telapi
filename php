<? php

('BOT_TOKEN', '12345678: replace-me-con-real-token');
https://api.telegram.org/bot/getMe

Funzione apiRequestWebhook ($ metodo, i parametri di $) {
  if (! is_string ($ metodo)) {
    error_log ("Nome metodo deve essere una stringa \ n");
    return false;
  }

  if (! $) {parametri
    $ parametri = array ();
  } Else if (! $ Is_array (parametri)) {
    error_log ("Parametri deve essere un array \ n");
    return false;
  }

  $ parametri ["metodo"] = $ metodo;

  header ("Content-Type: application / json");
  json_encode echo ($ parametri);
  return true;
}

Funzione exec_curl_request ($ maniglia) {
  $ risposta = curl_exec ($ maniglia);

  if ($ risposta === false) {
    $ errno = curl_errno ($ maniglia);
    $ error = curl_error ($ maniglia);
    error_log ("Curl ha restituito l'errore $ errno: $ error \ n");
    curl_close ($ maniglia);
    return false;
  }

  $ http_code = intval (curl_getinfo ($ maniglia, CURLINFO_HTTP_CODE));
  curl_close ($ maniglia);

  if ($ http_code> = 500) {
    // Non wat al server DDOS se qualcosa va storto
    sleep (10);
    return false;
  } Else if ($ http_code! = 200) {
    $ risposta = json_decode ($ risposta, true);
    error_log ("Richiesta non riuscita con errore {$ risposta ['error_code']}: {$ risposta ['descrizione']} \ n");
    if ($ http_code == 401) {
      throw new Exception ('accesso valido gettone fornito');
    }
    return false;
  } altro {
    $ risposta = json_decode ($ risposta, true);
    if (isset ($ risposta ['descrizione'])) {
      error_log ("Richiesta era successo: {$ risposta ['descrizione']} \ n");
    }
    $ risposta = $ risposta ['risultato'];
  }

  return $ risposta;
}

Funzione apiRequest ($ metodo, i parametri di $) {
  if (! is_string ($ metodo)) {
    error_log ("Nome metodo deve essere una stringa \ n");
    return false;
  }

  if (! $) {parametri
    $ parametri = array ();
  } Else if (! $ Is_array (parametri)) {
    error_log ("Parametri deve essere un array \ n");
    return false;
  }

  foreach ($ parametri come $ key => & $ val) {
    // Codifica parametri array JSON, ad esempio reply_markup
    if (! is_numeric ($ val) &&! is_string ($ val)) {
      $ val = json_encode ($ val);
    }
  }
  $ $ metodo url = API_URL http_build_query ($ parametri).. '?'.;

  $ maniglia = curl_init ($ url);
  curl_setopt ($ maniglia, CURLOPT_RETURNTRANSFER, true);
  curl_setopt ($ handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt ($ handle, CURLOPT_TIMEOUT, 60);

  tornare exec_curl_request ($ maniglia);
}

Funzione apiRequestJson ($ metodo, i parametri di $) {
  if (! is_string ($ metodo)) {
    error_log ("Nome metodo deve essere una stringa \ n");
    return false;
  }

  if (! $) {parametri
    $ parametri = array ();
  } Else if (! $ Is_array (parametri)) {
    error_log ("Parametri deve essere un array \ n");
    return false;
  }

  $ parametri ["metodo"] = $ metodo;

  $ maniglia = curl_init (API_URL);
  curl_setopt ($ maniglia, CURLOPT_RETURNTRANSFER, true);
  curl_setopt ($ handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt ($ handle, CURLOPT_TIMEOUT, 60);
  curl_setopt ($ maniglia, CURLOPT_POSTFIELDS, json_encode (parametri $));
  curl_setopt ($ maniglia, CURLOPT_HTTPHEADER, array ("Content-Type: application / json"));

  tornare exec_curl_request ($ maniglia);
}

funzione ProcessMessage ($ messaggio) {
  // Processo messaggio in arrivo
  $ message_id = $ messaggio ['message_ID'];
  $ chat_id = $ messaggio ['chat'] ['id'];
  if (isset ($ message ['testo'])) {
    // SMS in arrivo
    $ text = $ messaggio ['testo'];

    if (strpos ($ testo, "/ inizio") === 0) {
      apiRequestJson ("sendMessage", array ('chat_id' => $ chat_id, "testo" => 'Ciao', 'reply_markup' => array (
        'tastiera' => array (array ('Ciao', 'Ciao')),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));
    } Else if ($ testo === "Ciao" || $ testo === "Ciao") {
      apiRequest ("sendMessage", array ('chat_id' => $ chat_id, "testo" => 'Piacere di conoscerti'));
    } Else if (strpos ($ testo, "/ stop") === 0) {
      // fermati adesso
    } altro {
      apiRequestWebhook ("sendMessage", array ('chat_id' => $ chat_id, "reply_to_message_id" => $ message_id, "testo" => 'Cool'));
    }
  } altro {
    apiRequest ("sendMessage", array ('chat_id' => $ chat_id, "testo" => 'capisco solo messaggi di testo'));
  }
}


define ('WEBHOOK_URL', 'https://my-site.example.com/secret-path-for-webhooks/');

if (php_sapi_name () == 'cli') {
  // Se eseguito da console, impostare o eliminare webhook
  apiRequest ('setWebhook', array ('url' => isset ($ argv [1]) && $ argv [1] == 'cancellare' '':? WEBHOOK_URL));
  Uscita;
}


$ content = file_get_contents ("php: // input");
$ update = json_decode ($ content, true);

if (! $ update) {
  // Ricevere l'aggiornamento sbagliato, non deve accadere
  Uscita;
}

if (isset ($ aggiornamento ["messaggio"])) {
  ProcessMessage ($ aggiornamento ["messaggio"]);
}
