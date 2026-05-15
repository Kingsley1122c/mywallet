// Helper to get display sender name (first name or full name)
function getDisplaySenderName(senderName, senderEmail) {
  if (!senderName) {
    if (senderEmail) return senderEmail.split('@')[0];
    return '';
  }
  // If senderName contains a space, use first word (first name)
  if (typeof senderName === 'string' && senderName.trim().length > 0) {
    const parts = senderName.trim().split(' ');
    return parts[0];
  }
  return senderName;
}

function translateUi(key, fallback) {
  if (window.i18n && typeof window.i18n.t === 'function') {
    const translated = window.i18n.t(key);
    if (translated && translated !== key) {
      return translated;
    }
  }
  return fallback;
}

function buildWithdrawalLoadingMarkup() {
  const processingLabel = getBankUiText('processing');
  const loadingDescription = getBankUiText('withdrawLoadingDescription');
  const codeCheckLabel = getBankUiText('withdrawLoadingCodeCheck');
  const secureQueueLabel = getBankUiText('withdrawLoadingSecureQueue');
  const statusUpdateLabel = getBankUiText('withdrawLoadingStatusUpdate');
  return `
    <div style="background:linear-gradient(135deg,#eff6ff 0%,#f8fafc 100%);border:1px solid #bfdbfe;border-radius:18px;padding:18px 16px;box-shadow:0 10px 30px rgba(2,132,199,0.08);">
      <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:14px;">
        <div style="display:inline-block;width:30px;height:30px;border:4px solid #dbeafe;border-top-color:#0284c7;border-radius:50%;animation:spin 0.8s linear infinite;"></div>
        <div style="text-align:left;">
          <div style="color:#0284c7;font-weight:800;font-size:16px;">${processingLabel}</div>
          <div style="color:#475569;font-size:13px;">${loadingDescription}</div>
        </div>
      </div>
      <div style="height:8px;background:#dbeafe;border-radius:999px;overflow:hidden;margin-bottom:12px;">
        <div style="width:40%;height:100%;border-radius:999px;background:linear-gradient(90deg,#0284c7 0%,#38bdf8 50%,#7dd3fc 100%);animation:withdraw-progress 1.3s ease-in-out infinite;"></div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;text-align:center;">
        <div style="padding:10px 8px;border-radius:14px;background:rgba(255,255,255,0.72);color:#0f172a;font-size:12px;font-weight:700;">${codeCheckLabel}</div>
        <div style="padding:10px 8px;border-radius:14px;background:rgba(255,255,255,0.72);color:#0f172a;font-size:12px;font-weight:700;">${secureQueueLabel}</div>
        <div style="padding:10px 8px;border-radius:14px;background:rgba(255,255,255,0.72);color:#0f172a;font-size:12px;font-weight:700;">${statusUpdateLabel}</div>
      </div>
    </div>`;
}

const transactionText = {
  en: { received: 'Received from', sent: 'Sent to', added: 'Money added', addedByAdmin: 'Money added by admin', transferFrom: 'Transfer from', withdrawal: 'Withdrawal to', openingBalance: 'Opening balance', approved: 'Approved', pending: 'Pending', processing: 'Processing', failed: 'Failed' },
  es: { received: 'Recibido de', sent: 'Enviado a', added: 'Dinero agregado', addedByAdmin: 'Dinero agregado por el administrador', transferFrom: 'Transferencia de', withdrawal: 'Retiro a', openingBalance: 'Saldo inicial', approved: 'Aprobado', pending: 'Pendiente', processing: 'Procesando', failed: 'Fallido' },
  fr: { received: 'Recu de', sent: 'Envoye a', added: 'Argent ajoute', addedByAdmin: 'Argent ajoute par l administrateur', transferFrom: 'Transfert de', withdrawal: 'Retrait vers', openingBalance: 'Solde initial', approved: 'Approuve', pending: 'En attente', processing: 'En cours', failed: 'Echoue' },
  de: { received: 'Erhalten von', sent: 'Gesendet an', added: 'Geld hinzugefugt', addedByAdmin: 'Geld vom Administrator hinzugefugt', transferFrom: 'Uberweisung von', withdrawal: 'Auszahlung an', openingBalance: 'Anfangssaldo', approved: 'Genehmigt', pending: 'Ausstehend', processing: 'Wird bearbeitet', failed: 'Fehlgeschlagen' },
  it: { received: 'Ricevuto da', sent: 'Inviato a', added: 'Denaro aggiunto', addedByAdmin: 'Denaro aggiunto dall amministratore', transferFrom: 'Trasferimento da', withdrawal: 'Prelievo verso', openingBalance: 'Saldo iniziale', approved: 'Approvato', pending: 'In attesa', processing: 'In elaborazione', failed: 'Non riuscito' },
  pt: { received: 'Recebido de', sent: 'Enviado para', added: 'Dinheiro adicionado', addedByAdmin: 'Dinheiro adicionado pelo administrador', transferFrom: 'Transferencia de', withdrawal: 'Saque para', openingBalance: 'Saldo inicial', approved: 'Aprovado', pending: 'Pendente', processing: 'Processando', failed: 'Falhou' },
  ko: { received: 'δïñ∞¥î∞£╝δí£δ╢Çφä░ ∞êÿ∞ïá', sent: 'δïñ∞¥î∞£╝δí£ ∞áä∞åí', added: 'δÅê∞¥┤ ∞╢öΩ░ÇδÉ¿', addedByAdmin: 'Ω┤Çδª¼∞₧ÉΩ░Ç ∞╢öΩ░Çφò£ Ω╕ê∞òí', transferFrom: 'δïñ∞¥î∞£╝δí£δ╢Çφä░ ∞¥┤∞▓┤', withdrawal: 'δïñ∞¥î Ω│ä∞óîδí£ ∞╢£Ω╕ê', openingBalance: '∞┤êΩ╕░ ∞₧ö∞òí', approved: '∞è╣∞¥╕δÉ¿', pending: 'δîÇΩ╕░ ∞ñæ', processing: '∞▓ÿδª¼ ∞ñæ', failed: '∞ïñφî¿' },
  ja: { received: 'σÅùσÅûσàâ', sent: 'ΘÇüΘçæσàê', added: 'Φ│çΘçæπüîΦ┐╜σèáπüòπéîπü╛πüùπüƒ', addedByAdmin: 'τ«íτÉåΦÇàπüîΦ┐╜σèáπüùπüƒΦ│çΘçæ', transferFrom: 'ΘÇüΘçæσàâ', withdrawal: 'σç║Θçæσàê', openingBalance: 'Θûïσºïµ«ïΘ½ÿ', approved: 'µë┐Φ¬ìµ╕êπü┐', pending: 'Σ┐¥τòÖΣ╕¡', processing: 'σçªτÉåΣ╕¡', failed: 'σñ▒µòù' },
  'zh-tw': { received: 'Σ╛åΦç¬', sent: 'τÖ╝ΘÇüΦç│', added: 'σ╖▓µû░σó₧Φ│çΘçæ', addedByAdmin: 'τ«íτÉåσôíµû░σó₧τÜäΦ│çΘçæ', transferFrom: 'Φ╜ëσ╕│Φç¬', withdrawal: 'µÅÉµ¼╛Φç│', openingBalance: 'σê¥σºïΘñÿΘíì', approved: 'σ╖▓µá╕σçå', pending: 'σ╛àΦÖòτÉå', processing: 'ΦÖòτÉåΣ╕¡', failed: 'σñ▒µòù' },
  ar: { received: '╪¬┘à ╪º┘ä╪º╪│╪¬┘ä╪º┘à ┘à┘å', sent: '╪¬┘à ╪º┘ä╪Ñ╪▒╪│╪º┘ä ╪Ñ┘ä┘ë', added: '╪¬┘à╪¬ ╪Ñ╪╢╪º┘ü╪⌐ ╪º┘ä╪ú┘à┘ê╪º┘ä', addedByAdmin: '╪¬┘à╪¬ ╪Ñ╪╢╪º┘ü╪⌐ ╪º┘ä╪ú┘à┘ê╪º┘ä ╪¿┘ê╪º╪│╪╖╪⌐ ╪º┘ä┘à╪│╪ñ┘ê┘ä', transferFrom: '╪¬╪¡┘ê┘è┘ä ┘à┘å', withdrawal: '╪│╪¡╪¿ ╪Ñ┘ä┘ë', openingBalance: '╪º┘ä╪▒╪╡┘è╪» ╪º┘ä╪º┘ü╪¬╪¬╪º╪¡┘è', approved: '╪¬┘à╪¬ ╪º┘ä┘à┘ê╪º┘ü┘é╪⌐', pending: '┘é┘è╪» ╪º┘ä╪º┘å╪¬╪╕╪º╪▒', processing: '╪¼╪º╪▒ ╪º┘ä┘à╪╣╪º┘ä╪¼╪⌐', failed: '┘ü╪┤┘ä' },
  hi: { received: 'αñ╕αÑç αñ¬αÑìαñ░αñ╛αñ¬αÑìαññ', sent: 'αñòαÑï αñ¡αÑçαñ£αñ╛ αñùαñ»αñ╛', added: 'αñ¬αÑêαñ╕αÑç αñ£αÑïαñíαñ╝αÑç αñùαñÅ', addedByAdmin: 'αñÅαñíαñ«αñ┐αñ¿ αñªαÑìαñ╡αñ╛αñ░αñ╛ αñ£αÑïαñíαñ╝αÑç αñùαñÅ αñ¬αÑêαñ╕αÑç', transferFrom: 'αñ╕αÑç αñƒαÑìαñ░αñ╛αñéαñ╕αñ½αñ░', withdrawal: 'αñ¿αñ┐αñòαñ╛αñ╕αÑÇ αñ╣αÑçαññαÑü', openingBalance: 'αñ¬αÑìαñ░αñ╛αñ░αñéαñ¡αñ┐αñò αñ╢αÑçαñ╖', approved: 'αñ╕αÑìαñ╡αÑÇαñòαÑâαññ', pending: 'αñ▓αñéαñ¼αñ┐αññ', processing: 'αñ¬αÑìαñ░αñ╕αñéαñ╕αÑìαñòαñ░αñú αñ«αÑçαñé', failed: 'αñ╡αñ┐αñ½αñ▓' },
  ru: { received: '╨ƒ╨╛╨╗╤â╤ç╨╡╨╜╨╛ ╨╛╤é', sent: '╨₧╤é╨┐╤Ç╨░╨▓╨╗╨╡╨╜╨╛', added: '╨ö╨╡╨╜╤î╨│╨╕ ╨┤╨╛╨▒╨░╨▓╨╗╨╡╨╜╤ï', addedByAdmin: '╨ö╨╡╨╜╤î╨│╨╕ ╨┤╨╛╨▒╨░╨▓╨╗╨╡╨╜╤ï ╨░╨┤╨╝╨╕╨╜╨╕╤ü╤é╤Ç╨░╤é╨╛╤Ç╨╛╨╝', transferFrom: '╨ƒ╨╡╤Ç╨╡╨▓╨╛╨┤ ╨╛╤é', withdrawal: '╨Æ╤ï╨▓╨╛╨┤ ╨╜╨░', openingBalance: '╨¥╨░╤ç╨░╨╗╤î╨╜╤ï╨╣ ╨▒╨░╨╗╨░╨╜╤ü', approved: '╨₧╨┤╨╛╨▒╤Ç╨╡╨╜╨╛', pending: '╨Æ ╨╛╨╢╨╕╨┤╨░╨╜╨╕╨╕', processing: '╨₧╨▒╤Ç╨░╨▒╨░╤é╤ï╨▓╨░╨╡╤é╤ü╤Å', failed: '╨₧╤ê╨╕╨▒╨║╨░' },
  nl: { received: 'Ontvangen van', sent: 'Verzonden naar', added: 'Geld toegevoegd', addedByAdmin: 'Geld toegevoegd door beheerder', transferFrom: 'Overboeking van', withdrawal: 'Opname naar', openingBalance: 'Beginsaldo', approved: 'Goedgekeurd', pending: 'In behandeling', processing: 'Wordt verwerkt', failed: 'Mislukt' }
};

const supportLabelText = {
  en: 'Support',
  es: 'Soporte',
  fr: 'Support',
  de: 'Support',
  it: 'Supporto',
  pt: 'Suporte',
  ko: '∞ºÇ∞¢É',
  ja: 'πé╡πâ¥πâ╝πâê',
  'zh-tw': 'µö»µÅ┤',
  ar: '╪º┘ä╪»╪╣┘à',
  hi: 'αñ╕αñ╣αñ╛αñ»αññαñ╛',
  ru: '╨ƒ╨╛╨┤╨┤╨╡╤Ç╨╢╨║╨░',
  nl: 'Ondersteuning'
};

const bankUiText = {
  en: {
    requestWithdrawalCodeButton: 'Request Withdrawal Code',
    withdrawalCodeRequestPending: 'Your withdrawal code request has been sent. Enter the code here once you receive it.',
    withdrawalCodeRequestFailed: 'Unable to send the withdrawal code request right now. Please try again.',
    requestWithdrawalCodeFirst: 'Request a withdrawal code first before completing this withdrawal.',
    invalidOrExpiredCode: 'Codigo invalido o vencido.',
    networkErrorTryAgain: 'Error de red. Intentalo de nuevo.',
    withdrawLoadingDescription: 'Verificando tu codigo y preparando la solicitud de transferencia.',
    withdrawLoadingCodeCheck: 'Revision del codigo',
    withdrawLoadingSecureQueue: 'Cola segura',
    withdrawLoadingStatusUpdate: 'Actualizacion de estado'
  },
  fr: {
    addMoneyTitle: 'Ajouter de l argent',
    addMoneySubtitle: 'Deposez des fonds sur votre compte',
    enterAmount: 'Saisir le montant',
    selectPaymentMethod: 'Selectionnez un mode de paiement',
    cardOption: 'Carte',
    bankTransferOption: 'Virement bancaire',
    addMoneyButton: 'Ajouter de l argent',
    cancelButton: 'Annuler',
    processing: 'Traitement...',
    pleaseSelectPaymentMethod: 'Veuillez selectionner un mode de paiement',
    enterPositiveAmount: 'Saisissez un montant positif valide',
    maxAmount: 'Le montant maximum par transaction est de $10,000',
    validCardNumber: 'Veuillez saisir un numero de carte valide',
    validExpiryDate: 'Veuillez saisir une date d expiration valide (MM/AA)',
    validCvv: 'Veuillez saisir un CVV valide',
    cardholderName: 'Veuillez saisir le nom du titulaire',
    moneyAddedSuccess: 'Argent ajoute avec succes',
    networkError: 'Erreur reseau',
    securityTokenNotLoaded: 'Le jeton de securite n est pas encore charge. Veuillez patienter un instant et reessayer.',
    enterRecipientAndAmount: 'Saisissez un destinataire et un montant positif valide',
    insufficientBalance: 'Solde insuffisant',
    transferUnexpected: 'Le transfert est termine, mais le format de la reponse est inattendu. Actualisez la page.',
    networkErrorWithMessage: 'Erreur reseau : {message}',
    serverErrorWithMessage: 'Erreur du serveur : {message}',
    withdrawalExceedsBalance: 'Le montant du retrait depasse votre solde disponible.',
    validSixDigitCode: 'Veuillez saisir un code valide a 6 chiffres.',
    withdrawalFailed: 'Le retrait a echoue.',
    invalidOrExpiredCode: 'Code invalide ou expire.',
    networkErrorTryAgain: 'Erreur reseau. Veuillez reessayer.',
    withdrawLoadingDescription: 'Verification de votre code et preparation de la demande de transfert.',
    withdrawLoadingCodeCheck: 'Controle du code',
    withdrawLoadingSecureQueue: 'File securisee',
    withdrawLoadingStatusUpdate: 'Mise a jour du statut'
  },
  de: {
    addMoneyTitle: 'Geld hinzufugen',
    addMoneySubtitle: 'Zahlen Sie Geld auf Ihr Konto ein',
    enterAmount: 'Betrag eingeben',
    selectPaymentMethod: 'Zahlungsmethode auswahlen',
    cardOption: 'Karte',
    bankTransferOption: 'Bankuberweisung',
    addMoneyButton: 'Geld hinzufugen',
    cancelButton: 'Abbrechen',
    processing: 'Wird bearbeitet...',
    pleaseSelectPaymentMethod: 'Bitte wahlen Sie eine Zahlungsmethode aus',
    enterPositiveAmount: 'Geben Sie einen gultigen positiven Betrag ein',
    maxAmount: 'Der Hochstbetrag pro Transaktion betragt $10,000',
    validCardNumber: 'Bitte geben Sie eine gultige Kartennummer ein',
    validExpiryDate: 'Bitte geben Sie ein gultiges Ablaufdatum ein (MM/JJ)',
    validCvv: 'Bitte geben Sie eine gultige CVV ein',
    cardholderName: 'Bitte geben Sie den Namen des Karteninhabers ein',
    moneyAddedSuccess: 'Geld erfolgreich hinzugefugt',
    networkError: 'Netzwerkfehler',
    securityTokenNotLoaded: 'Das Sicherheitstoken wurde noch nicht geladen. Bitte warten Sie einen Moment und versuchen Sie es erneut.',
    enterRecipientAndAmount: 'Geben Sie einen Empfanger und einen gultigen positiven Betrag ein',
    insufficientBalance: 'Unzureichendes Guthaben',
    transferUnexpected: 'Die Uberweisung wurde abgeschlossen, aber das Antwortformat war unerwartet. Bitte aktualisieren Sie die Seite.',
    networkErrorWithMessage: 'Netzwerkfehler: {message}',
    serverErrorWithMessage: 'Serverfehler: {message}',
    withdrawalExceedsBalance: 'Der Auszahlungsbetrag ubersteigt Ihr verfugbares Guthaben.',
    validSixDigitCode: 'Bitte geben Sie einen gultigen 6-stelligen Code ein.',
    withdrawalFailed: 'Auszahlung fehlgeschlagen.',
    invalidOrExpiredCode: 'Code ungultig oder abgelaufen.',
    networkErrorTryAgain: 'Netzwerkfehler. Bitte versuchen Sie es erneut.',
    withdrawLoadingDescription: 'Ihr Code wird gepruft und die Uberweisungsanfrage vorbereitet.',
    withdrawLoadingCodeCheck: 'Codeprufung',
    withdrawLoadingSecureQueue: 'Sichere Warteschlange',
    withdrawLoadingStatusUpdate: 'Statusaktualisierung'
  },
  it: {
    addMoneyTitle: 'Aggiungi denaro',
    addMoneySubtitle: 'Deposita fondi sul tuo conto',
    enterAmount: 'Inserisci importo',
    selectPaymentMethod: 'Seleziona metodo di pagamento',
    cardOption: 'Carta',
    bankTransferOption: 'Bonifico bancario',
    addMoneyButton: 'Aggiungi denaro',
    cancelButton: 'Annulla',
    processing: 'Elaborazione...',
    pleaseSelectPaymentMethod: 'Seleziona un metodo di pagamento',
    enterPositiveAmount: 'Inserisci un importo positivo valido',
    maxAmount: 'L importo massimo per transazione e $10,000',
    validCardNumber: 'Inserisci un numero di carta valido',
    validExpiryDate: 'Inserisci una data di scadenza valida (MM/AA)',
    validCvv: 'Inserisci un CVV valido',
    cardholderName: 'Inserisci il nome del titolare',
    moneyAddedSuccess: 'Denaro aggiunto con successo',
    networkError: 'Errore di rete',
    securityTokenNotLoaded: 'Il token di sicurezza non e stato ancora caricato. Attendi un momento e riprova.',
    enterRecipientAndAmount: 'Inserisci il destinatario e un importo positivo valido',
    insufficientBalance: 'Saldo insufficiente',
    transferUnexpected: 'Il trasferimento e stato completato, ma il formato della risposta non era previsto. Aggiorna la pagina.',
    networkErrorWithMessage: 'Errore di rete: {message}',
    serverErrorWithMessage: 'Errore del server: {message}',
    withdrawalExceedsBalance: 'L importo del prelievo supera il saldo disponibile.',
    validSixDigitCode: 'Inserisci un codice valido di 6 cifre.',
    withdrawalFailed: 'Prelievo non riuscito.',
    invalidOrExpiredCode: 'Codice non valido o scaduto.',
    networkErrorTryAgain: 'Errore di rete. Riprova.',
    withdrawLoadingDescription: 'Verifica del codice e preparazione della richiesta di trasferimento.',
    withdrawLoadingCodeCheck: 'Controllo codice',
    withdrawLoadingSecureQueue: 'Coda sicura',
    withdrawLoadingStatusUpdate: 'Aggiornamento stato'
  },
  pt: {
    addMoneyTitle: 'Adicionar dinheiro',
    addMoneySubtitle: 'Deposite fundos na sua conta',
    enterAmount: 'Digite o valor',
    selectPaymentMethod: 'Selecione o metodo de pagamento',
    cardOption: 'Cartao',
    bankTransferOption: 'Transferencia bancaria',
    addMoneyButton: 'Adicionar dinheiro',
    cancelButton: 'Cancelar',
    processing: 'Processando...',
    pleaseSelectPaymentMethod: 'Selecione um metodo de pagamento',
    enterPositiveAmount: 'Digite um valor positivo valido',
    maxAmount: 'O valor maximo por transacao e $10,000',
    validCardNumber: 'Digite um numero de cartao valido',
    validExpiryDate: 'Digite uma data de validade valida (MM/AA)',
    validCvv: 'Digite um CVV valido',
    cardholderName: 'Digite o nome do titular do cartao',
    moneyAddedSuccess: 'Dinheiro adicionado com sucesso',
    networkError: 'Erro de rede',
    securityTokenNotLoaded: 'O token de seguranca ainda nao foi carregado. Aguarde um momento e tente novamente.',
    enterRecipientAndAmount: 'Digite o destinatario e um valor positivo valido',
    insufficientBalance: 'Saldo insuficiente',
    transferUnexpected: 'A transferencia foi concluida, mas o formato da resposta foi inesperado. Atualize a pagina.',
    networkErrorWithMessage: 'Erro de rede: {message}',
    serverErrorWithMessage: 'Erro do servidor: {message}',
    withdrawalExceedsBalance: 'O valor do saque excede seu saldo disponivel.',
    validSixDigitCode: 'Digite um codigo valido de 6 digitos.',
    withdrawalFailed: 'Falha no saque.',
    invalidOrExpiredCode: 'Codigo invalido ou expirado.',
    networkErrorTryAgain: 'Erro de rede. Tente novamente.',
    withdrawLoadingDescription: 'Verificando seu codigo e preparando a solicitacao de transferencia.',
    withdrawLoadingCodeCheck: 'Verificacao do codigo',
    withdrawLoadingSecureQueue: 'Fila segura',
    withdrawLoadingStatusUpdate: 'Atualizacao de status'
  },
  ko: {
    addMoneyTitle: '∞₧ÉΩ╕ê ∞╢öΩ░Ç',
    addMoneySubtitle: 'Ω│ä∞áò∞ùÉ ∞₧ÉΩ╕ê∞¥ä ∞₧àΩ╕êφòÿ∞ä╕∞Üö',
    enterAmount: 'Ω╕ê∞òí ∞₧àδáÑ',
    selectPaymentMethod: 'Ω▓░∞á£ δ░⌐δ▓ò ∞äáφâ¥',
    cardOption: '∞╣┤δô£',
    bankTransferOption: '∞¥Çφûë ∞åíΩ╕ê',
    addMoneyButton: '∞₧ÉΩ╕ê ∞╢öΩ░Ç',
    cancelButton: '∞╖¿∞åî',
    processing: '∞▓ÿδª¼ ∞ñæ...',
    pleaseSelectPaymentMethod: 'Ω▓░∞á£ δ░⌐δ▓ò∞¥ä ∞äáφâ¥φòÿ∞ä╕∞Üö',
    enterPositiveAmount: '∞£áφÜ¿φò£ ∞ûæ∞êÿ Ω╕ê∞òí∞¥ä ∞₧àδáÑφòÿ∞ä╕∞Üö',
    maxAmount: 'Ω▒░δ₧ÿδï╣ ∞╡£δîÇ Ω╕ê∞òí∞¥Ç $10,000∞₧àδïêδïñ',
    validCardNumber: '∞£áφÜ¿φò£ ∞╣┤δô£ δ▓êφÿ╕δÑ╝ ∞₧àδáÑφòÿ∞ä╕∞Üö',
    validExpiryDate: '∞£áφÜ¿φò£ δºîδúî∞¥╝∞¥ä ∞₧àδáÑφòÿ∞ä╕∞Üö (MM/YY)',
    validCvv: '∞£áφÜ¿φò£ CVVδÑ╝ ∞₧àδáÑφòÿ∞ä╕∞Üö',
    cardholderName: '∞╣┤δô£ ∞åî∞£á∞₧É ∞¥┤δªä∞¥ä ∞₧àδáÑφòÿ∞ä╕∞Üö',
    moneyAddedSuccess: '∞₧ÉΩ╕ê∞¥┤ ∞ä▒Ω│╡∞áü∞£╝δí£ ∞╢öΩ░ÇδÉÿ∞ùê∞è╡δïêδïñ',
    networkError: 'δäñφè╕∞¢îφü¼ ∞ÿñδÑÿ',
    securityTokenNotLoaded: 'δ│┤∞òê φåáφü░∞¥┤ ∞òä∞ºü δí£δô£δÉÿ∞ºÇ ∞òè∞òÿ∞è╡δïêδïñ. ∞₧á∞ï£ φ¢ä δïñ∞ï£ ∞ï£δÅäφòÿ∞ä╕∞Üö.',
    enterRecipientAndAmount: '∞êÿ∞ïá∞₧É∞ÖÇ ∞£áφÜ¿φò£ ∞ûæ∞êÿ Ω╕ê∞òí∞¥ä ∞₧àδáÑφòÿ∞ä╕∞Üö',
    insufficientBalance: '∞₧ö∞òí∞¥┤ δ╢Ç∞í▒φò⌐δïêδïñ',
    transferUnexpected: '∞¥┤∞▓┤Ω░Ç ∞ÖäδúîδÉÿ∞ùê∞ºÇδºî ∞¥æδï╡ φÿò∞ï¥∞¥┤ ∞ÿê∞âüΩ│╝ δïñδªàδïêδïñ. φÄÿ∞¥┤∞ºÇδÑ╝ ∞âêδí£Ω│á∞╣¿φòÿ∞ä╕∞Üö.',
    networkErrorWithMessage: 'δäñφè╕∞¢îφü¼ ∞ÿñδÑÿ: {message}',
    serverErrorWithMessage: '∞ä£δ▓ä ∞ÿñδÑÿ: {message}',
    withdrawalExceedsBalance: '∞╢£Ω╕ê Ω╕ê∞òí∞¥┤ ∞é¼∞Ü⌐ Ω░ÇδèÑφò£ ∞₧ö∞òí∞¥ä ∞┤êΩ│╝φò⌐δïêδïñ.',
    validSixDigitCode: '∞£áφÜ¿φò£ 6∞₧Éδª¼ ∞╜öδô£δÑ╝ ∞₧àδáÑφòÿ∞ä╕∞Üö.',
    withdrawalFailed: '∞╢£Ω╕ê∞ùÉ ∞ïñφî¿φûê∞è╡δïêδïñ.',
    invalidOrExpiredCode: '∞╜öδô£Ω░Ç ∞£áφÜ¿φòÿ∞ºÇ ∞òèΩ▒░δéÿ δºîδúîδÉÿ∞ùê∞è╡δïêδïñ.',
    networkErrorTryAgain: 'δäñφè╕∞¢îφü¼ ∞ÿñδÑÿ∞₧àδïêδïñ. δïñ∞ï£ ∞ï£δÅäφòÿ∞ä╕∞Üö.',
    withdrawLoadingDescription: '∞╜öδô£δÑ╝ φÖò∞¥╕φòÿΩ│á ∞¥┤∞▓┤ ∞Üö∞▓¡∞¥ä ∞ñÇδ╣äφòÿΩ│á ∞₧ê∞è╡δïêδïñ.',
    withdrawLoadingCodeCheck: '∞╜öδô£ φÖò∞¥╕',
    withdrawLoadingSecureQueue: 'δ│┤∞òê δîÇΩ╕░∞ù┤',
    withdrawLoadingStatusUpdate: '∞âüφâ£ ∞ùàδì░∞¥┤φè╕'
  },
  ja: {
    addMoneyTitle: 'Φ│çΘçæπéÆΦ┐╜σèá',
    addMoneySubtitle: 'σÅúσ║ºπü½Φ│çΘçæπéÆσàÑΘçæπüùπü╛πüÖ',
    enterAmount: 'ΘçæΘíìπéÆσàÑσè¢',
    selectPaymentMethod: 'µö»µëòπüäµû╣µ│òπéÆΘü╕µè₧',
    cardOption: 'πé½πâ╝πâë',
    bankTransferOption: 'ΘèÇΦíîµî»Φ╛╝',
    addMoneyButton: 'Φ│çΘçæπéÆΦ┐╜σèá',
    cancelButton: 'πé¡πâúπâ│πé╗πâ½',
    processing: 'σçªτÉåΣ╕¡...',
    pleaseSelectPaymentMethod: 'µö»µëòπüäµû╣µ│òπéÆΘü╕µè₧πüùπüªπüÅπüáπüòπüä',
    enterPositiveAmount: 'µ£ëσè╣πü¬µ¡úπü«ΘçæΘíìπéÆσàÑσè¢πüùπüªπüÅπüáπüòπüä',
    maxAmount: '1σ¢₧πü«σÅûσ╝òπüéπüƒπéèπü«Σ╕èΘÖÉΘíìπü»$10,000πüºπüÖ',
    validCardNumber: 'µ£ëσè╣πü¬πé½πâ╝πâëτò¬σÅ╖πéÆσàÑσè¢πüùπüªπüÅπüáπüòπüä',
    validExpiryDate: 'µ£ëσè╣πü¬µ£ëσè╣µ£ƒΘÖÉπéÆσàÑσè¢πüùπüªπüÅπüáπüòπüä (MM/YY)',
    validCvv: 'µ£ëσè╣πü¬CVVπéÆσàÑσè¢πüùπüªπüÅπüáπüòπüä',
    cardholderName: 'πé½πâ╝πâëσÉìτ╛⌐Σ║║πéÆσàÑσè¢πüùπüªπüÅπüáπüòπüä',
    moneyAddedSuccess: 'Φ│çΘçæπüîµ¡úσ╕╕πü½Φ┐╜σèáπüòπéîπü╛πüùπüƒ',
    networkError: 'πâìπââπâêπâ»πâ╝πé»πé¿πâ⌐πâ╝',
    securityTokenNotLoaded: 'πé╗πé¡πâÑπâ¬πâåπéúπâêπâ╝πé»πâ│πüîπü╛πüáΦ¬¡πü┐Φ╛╝πü╛πéîπüªπüäπü╛πü¢πéôπÇéσ░æπüùσ╛àπüúπüªπüïπéëσåìΦ⌐ªΦíîπüùπüªπüÅπüáπüòπüäπÇé',
    enterRecipientAndAmount: 'σÅùσÅûΣ║║πü¿µ£ëσè╣πü¬µ¡úπü«ΘçæΘíìπéÆσàÑσè¢πüùπüªπüÅπüáπüòπüä',
    insufficientBalance: 'µ«ïΘ½ÿΣ╕ìΦ╢│πüºπüÖ',
    transferUnexpected: 'ΘÇüΘçæπü»σ«îΣ║åπüùπü╛πüùπüƒπüîπÇüσ┐£τ¡öσ╜óσ╝ÅπüîΣ║êµ£ƒπüùπüƒπééπü«πüºπü»πüéπéèπü╛πü¢πéôπÇéπâÜπâ╝πé╕πéÆµ¢┤µû░πüùπüªπüÅπüáπüòπüäπÇé',
    networkErrorWithMessage: 'πâìπââπâêπâ»πâ╝πé»πé¿πâ⌐πâ╝: {message}',
    serverErrorWithMessage: 'πé╡πâ╝πâÉπâ╝πé¿πâ⌐πâ╝: {message}',
    withdrawalExceedsBalance: 'σç║ΘçæΘíìπüîσê⌐τö¿σÅ»Φâ╜µ«ïΘ½ÿπéÆΦ╢àπüêπüªπüäπü╛πüÖπÇé',
    validSixDigitCode: 'µ£ëσè╣πü¬6µíüπü«πé│πâ╝πâëπéÆσàÑσè¢πüùπüªπüÅπüáπüòπüäπÇé',
    withdrawalFailed: 'σç║Θçæπü½σñ▒µòùπüùπü╛πüùπüƒπÇé',
    invalidOrExpiredCode: 'πé│πâ╝πâëπüîτäíσè╣πüïµ£ƒΘÖÉσêçπéîπüºπüÖπÇé',
    networkErrorTryAgain: 'πâìπââπâêπâ»πâ╝πé»πé¿πâ⌐πâ╝πüºπüÖπÇéπééπüåΣ╕Çσ║ªπüèΦ⌐ªπüùπüÅπüáπüòπüäπÇé',
    withdrawLoadingDescription: 'πé│πâ╝πâëπéÆτó║Φ¬ìπüùπÇüΘÇüΘçæπâ¬πé»πé¿πé╣πâêπéÆµ║ûσéÖπüùπüªπüäπü╛πüÖπÇé',
    withdrawLoadingCodeCheck: 'πé│πâ╝πâëτó║Φ¬ì',
    withdrawLoadingSecureQueue: 'σ«ëσà¿πü¬πé¡πâÑπâ╝',
    withdrawLoadingStatusUpdate: 'πé╣πâåπâ╝πé┐πé╣µ¢┤µû░'
  },
  'zh-tw': {
    addMoneyTitle: 'µû░σó₧Φ│çΘçæ',
    addMoneySubtitle: 'σ░çΦ│çΘçæσ¡ÿσàÑµé¿τÜäσ╕│µê╢',
    enterAmount: 'Φ╝╕σàÑΘçæΘíì',
    selectPaymentMethod: 'Θü╕µôçΣ╗ÿµ¼╛µû╣σ╝Å',
    cardOption: 'σìíτëç',
    bankTransferOption: 'ΘèÇΦíîΦ╜ëσ╕│',
    addMoneyButton: 'µû░σó₧Φ│çΘçæ',
    cancelButton: 'σÅûµ╢ê',
    processing: 'ΦÖòτÉåΣ╕¡...',
    pleaseSelectPaymentMethod: 'Φ½ïΘü╕µôçΣ╗ÿµ¼╛µû╣σ╝Å',
    enterPositiveAmount: 'Φ½ïΦ╝╕σàÑµ£ëµòêτÜäµ¡úµò╕ΘçæΘíì',
    maxAmount: 'µ»Åτ¡åΣ║ñµÿôτÜäµ£ÇΘ½ÿΘçæΘíìτé║ $10,000',
    validCardNumber: 'Φ½ïΦ╝╕σàÑµ£ëµòêτÜäσìíΦÖƒ',
    validExpiryDate: 'Φ½ïΦ╝╕σàÑµ£ëµòêτÜäσê░µ£ƒµùÑ (MM/YY)',
    validCvv: 'Φ½ïΦ╝╕σàÑµ£ëµòêτÜä CVV',
    cardholderName: 'Φ½ïΦ╝╕σàÑµîüσìíΣ║║σºôσÉì',
    moneyAddedSuccess: 'Φ│çΘçæσ╖▓µêÉσèƒµû░σó₧',
    networkError: 'τ╢▓Φ╖»Θî»Φ¬ñ',
    securityTokenNotLoaded: 'σ«ëσà¿µ¼èµ¥ûσ░Üµ£¬Φ╝ëσàÑπÇéΦ½ïτ¿ìσÇÖσåìΦ⌐ªπÇé',
    enterRecipientAndAmount: 'Φ½ïΦ╝╕σàÑµö╢µ¼╛Σ║║σÆîµ£ëµòêτÜäµ¡úµò╕ΘçæΘíì',
    insufficientBalance: 'ΘñÿΘíìΣ╕ìΦ╢│',
    transferUnexpected: 'Φ╜ëσ╕│σ╖▓σ«îµêÉ∩╝îΣ╜åσ¢₧µçëµá╝σ╝ÅΣ╕ìτ¼ªσÉêΘáÉµ£ƒπÇéΦ½ïΘçìµû░µò┤τÉåΘáüΘ¥óπÇé',
    networkErrorWithMessage: 'τ╢▓Φ╖»Θî»Φ¬ñ: {message}',
    serverErrorWithMessage: 'Σ╝║µ£ìσÖ¿Θî»Φ¬ñ: {message}',
    withdrawalExceedsBalance: 'µÅÉµ¼╛ΘçæΘíìΦ╢àΘüÄµé¿τÜäσÅ»τö¿ΘñÿΘíìπÇé',
    validSixDigitCode: 'Φ½ïΦ╝╕σàÑµ£ëµòêτÜä 6 Σ╜ìµò╕Σ╗úτó╝πÇé',
    withdrawalFailed: 'µÅÉµ¼╛σñ▒µòùπÇé',
    invalidOrExpiredCode: 'Σ╗úτó╝τäíµòêµêûσ╖▓ΘüÄµ£ƒπÇé',
    networkErrorTryAgain: 'τ╢▓Φ╖»Θî»Φ¬ñπÇéΦ½ïσåìΦ⌐ªΣ╕Çµ¼íπÇé',
    withdrawLoadingDescription: 'µ¡úσ£¿Θ⌐ùΦ¡ëµé¿τÜäΣ╗úτó╝Σ╕ªµ║ûσéÖΦ╜ëσ╕│Φ½ïµ▒éπÇé',
    withdrawLoadingCodeCheck: 'Σ╗úτó╝µ¬óµƒÑ',
    withdrawLoadingSecureQueue: 'σ«ëσà¿Σ╜çσêù',
    withdrawLoadingStatusUpdate: 'τïÇµàïµ¢┤µû░'
  },
  ar: {
    addMoneyTitle: '╪Ñ╪╢╪º┘ü╪⌐ ╪ú┘à┘ê╪º┘ä',
    addMoneySubtitle: '╪ú┘ê╪»╪╣ ╪ú┘à┘ê╪º┘ä╪º┘ï ┘ü┘è ╪¡╪│╪º╪¿┘â',
    enterAmount: '╪ú╪»╪«┘ä ╪º┘ä┘à╪¿┘ä╪║',
    selectPaymentMethod: '╪º╪«╪¬╪▒ ╪╖╪▒┘è┘é╪⌐ ╪º┘ä╪»┘ü╪╣',
    cardOption: '╪¿╪╖╪º┘é╪⌐',
    bankTransferOption: '╪¬╪¡┘ê┘è┘ä ╪¿┘å┘â┘è',
    addMoneyButton: '╪Ñ╪╢╪º┘ü╪⌐ ╪ú┘à┘ê╪º┘ä',
    cancelButton: '╪Ñ┘ä╪║╪º╪í',
    processing: '╪¼╪º╪▒ ╪º┘ä┘à╪╣╪º┘ä╪¼╪⌐...',
    pleaseSelectPaymentMethod: '┘è╪▒╪¼┘ë ╪º╪«╪¬┘è╪º╪▒ ╪╖╪▒┘è┘é╪⌐ ╪»┘ü╪╣',
    enterPositiveAmount: '╪ú╪»╪«┘ä ┘à╪¿┘ä╪║╪º┘ï ┘à┘ê╪¼╪¿╪º┘ï ╪╡╪º┘ä╪¡╪º┘ï',
    maxAmount: '╪º┘ä╪¡╪» ╪º┘ä╪ú┘é╪╡┘ë ┘ä┘â┘ä ┘à╪╣╪º┘à┘ä╪⌐ ┘ç┘ê $10,000',
    validCardNumber: '┘è╪▒╪¼┘ë ╪Ñ╪»╪«╪º┘ä ╪▒┘é┘à ╪¿╪╖╪º┘é╪⌐ ╪╡╪º┘ä╪¡',
    validExpiryDate: '┘è╪▒╪¼┘ë ╪Ñ╪»╪«╪º┘ä ╪¬╪º╪▒┘è╪« ╪º┘å╪¬┘ç╪º╪í ╪╡╪º┘ä╪¡ (MM/YY)',
    validCvv: '┘è╪▒╪¼┘ë ╪Ñ╪»╪«╪º┘ä CVV ╪╡╪º┘ä╪¡',
    cardholderName: '┘è╪▒╪¼┘ë ╪Ñ╪»╪«╪º┘ä ╪º╪│┘à ╪¡╪º┘à┘ä ╪º┘ä╪¿╪╖╪º┘é╪⌐',
    moneyAddedSuccess: '╪¬┘à╪¬ ╪Ñ╪╢╪º┘ü╪⌐ ╪º┘ä╪ú┘à┘ê╪º┘ä ╪¿┘å╪¼╪º╪¡',
    networkError: '╪«╪╖╪ú ┘ü┘è ╪º┘ä╪┤╪¿┘â╪⌐',
    securityTokenNotLoaded: '┘ä┘à ┘è╪¬┘à ╪¬╪¡┘à┘è┘ä ╪▒┘à╪▓ ╪º┘ä╪ú┘à╪º┘å ╪¿╪╣╪». ┘è╪▒╪¼┘ë ╪º┘ä╪º┘å╪¬╪╕╪º╪▒ ┘é┘ä┘è┘ä╪º┘ï ╪½┘à ╪º┘ä┘à╪¡╪º┘ê┘ä╪⌐ ┘à╪▒╪⌐ ╪ú╪«╪▒┘ë.',
    enterRecipientAndAmount: '╪ú╪»╪«┘ä ╪º┘ä┘à╪│╪¬┘ä┘à ┘ê┘à╪¿┘ä╪║╪º┘ï ┘à┘ê╪¼╪¿╪º┘ï ╪╡╪º┘ä╪¡╪º┘ï',
    insufficientBalance: '╪º┘ä╪▒╪╡┘è╪» ╪║┘è╪▒ ┘â╪º┘ü',
    transferUnexpected: '╪º┘â╪¬┘à┘ä ╪º┘ä╪¬╪¡┘ê┘è┘ä ┘ê┘ä┘â┘å ╪¬┘å╪│┘è┘é ╪º┘ä╪º╪│╪¬╪¼╪º╪¿╪⌐ ╪║┘è╪▒ ┘à╪¬┘ê┘é╪╣. ┘è╪▒╪¼┘ë ╪¬╪¡╪»┘è╪½ ╪º┘ä╪╡┘ü╪¡╪⌐.',
    networkErrorWithMessage: '╪«╪╖╪ú ┘ü┘è ╪º┘ä╪┤╪¿┘â╪⌐: {message}',
    serverErrorWithMessage: '╪«╪╖╪ú ┘ü┘è ╪º┘ä╪«╪º╪»┘à: {message}',
    withdrawalExceedsBalance: '┘à╪¿┘ä╪║ ╪º┘ä╪│╪¡╪¿ ┘è╪¬╪¼╪º┘ê╪▓ ╪▒╪╡┘è╪»┘â ╪º┘ä┘à╪¬╪º╪¡.',
    validSixDigitCode: '┘è╪▒╪¼┘ë ╪Ñ╪»╪«╪º┘ä ╪▒┘à╪▓ ╪╡╪º┘ä╪¡ ┘à┘â┘ê┘å ┘à┘å 6 ╪ú╪▒┘é╪º┘à.',
    withdrawalFailed: '┘ü╪┤┘ä ╪º┘ä╪│╪¡╪¿.',
    invalidOrExpiredCode: '╪º┘ä╪▒┘à╪▓ ╪║┘è╪▒ ╪╡╪º┘ä╪¡ ╪ú┘ê ┘à┘å╪¬┘ç┘è ╪º┘ä╪╡┘ä╪º╪¡┘è╪⌐.',
    networkErrorTryAgain: '╪«╪╖╪ú ┘ü┘è ╪º┘ä╪┤╪¿┘â╪⌐. ┘è╪▒╪¼┘ë ╪º┘ä┘à╪¡╪º┘ê┘ä╪⌐ ┘à╪▒╪⌐ ╪ú╪«╪▒┘ë.',
    withdrawLoadingDescription: '╪¼╪º╪▒ ╪º┘ä╪¬╪¡┘é┘é ┘à┘å ╪º┘ä╪▒┘à╪▓ ╪º┘ä╪«╪º╪╡ ╪¿┘â ┘ê╪¬╪¼┘ç┘è╪▓ ╪╖┘ä╪¿ ╪º┘ä╪¬╪¡┘ê┘è┘ä.',
    withdrawLoadingCodeCheck: '┘ü╪¡╪╡ ╪º┘ä╪▒┘à╪▓',
    withdrawLoadingSecureQueue: '┘é╪º╪ª┘à╪⌐ ╪ó┘à┘å╪⌐',
    withdrawLoadingStatusUpdate: '╪¬╪¡╪»┘è╪½ ╪º┘ä╪¡╪º┘ä╪⌐'
  },
  hi: {
    addMoneyTitle: 'αñ¬αÑêαñ╕αÑç αñ£αÑïαñíαñ╝αÑçαñé',
    addMoneySubtitle: 'αñàαñ¬αñ¿αÑç αñûαñ╛αññαÑç αñ«αÑçαñé αñºαñ¿ αñ£αñ«αñ╛ αñòαñ░αÑçαñé',
    enterAmount: 'αñ░αñ╛αñ╢αñ┐ αñªαñ░αÑìαñ£ αñòαñ░αÑçαñé',
    selectPaymentMethod: 'αñ¡αÑüαñùαññαñ╛αñ¿ αñ╡αñ┐αñºαñ┐ αñÜαÑüαñ¿αÑçαñé',
    cardOption: 'αñòαñ╛αñ░αÑìαñí',
    bankTransferOption: 'αñ¼αÑêαñéαñò αñƒαÑìαñ░αñ╛αñéαñ╕αñ½αñ░',
    addMoneyButton: 'αñ¬αÑêαñ╕αÑç αñ£αÑïαñíαñ╝αÑçαñé',
    cancelButton: 'αñ░αñªαÑìαñª αñòαñ░αÑçαñé',
    processing: 'αñ¬αÑìαñ░αÑïαñ╕αÑçαñ╕ αñ╣αÑï αñ░αñ╣αñ╛ αñ╣αÑê...',
    pleaseSelectPaymentMethod: 'αñòαÑâαñ¬αñ»αñ╛ αñ¡αÑüαñùαññαñ╛αñ¿ αñ╡αñ┐αñºαñ┐ αñÜαÑüαñ¿αÑçαñé',
    enterPositiveAmount: 'αñ«αñ╛αñ¿αÑìαñ» αñºαñ¿αñ╛αññαÑìαñ«αñò αñ░αñ╛αñ╢αñ┐ αñªαñ░αÑìαñ£ αñòαñ░αÑçαñé',
    maxAmount: 'αñ¬αÑìαñ░αññαñ┐ αñ▓αÑçαñ¿αñªαÑçαñ¿ αñàαñºαñ┐αñòαññαñ« αñ░αñ╛αñ╢αñ┐ $10,000 αñ╣αÑê',
    validCardNumber: 'αñòαÑâαñ¬αñ»αñ╛ αñ«αñ╛αñ¿αÑìαñ» αñòαñ╛αñ░αÑìαñí αñ¿αñéαñ¼αñ░ αñªαñ░αÑìαñ£ αñòαñ░αÑçαñé',
    validExpiryDate: 'αñòαÑâαñ¬αñ»αñ╛ αñ«αñ╛αñ¿αÑìαñ» αñ╕αñ«αñ╛αñ¬αÑìαññαñ┐ αññαñ┐αñÑαñ┐ αñªαñ░αÑìαñ£ αñòαñ░αÑçαñé (MM/YY)',
    validCvv: 'αñòαÑâαñ¬αñ»αñ╛ αñ«αñ╛αñ¿αÑìαñ» CVV αñªαñ░αÑìαñ£ αñòαñ░αÑçαñé',
    cardholderName: 'αñòαÑâαñ¬αñ»αñ╛ αñòαñ╛αñ░αÑìαñíαñºαñ╛αñ░αñò αñòαñ╛ αñ¿αñ╛αñ« αñªαñ░αÑìαñ£ αñòαñ░αÑçαñé',
    moneyAddedSuccess: 'αñ¬αÑêαñ╕αÑç αñ╕αñ½αñ▓αññαñ╛αñ¬αÑéαñ░αÑìαñ╡αñò αñ£αÑïαñíαñ╝ αñªαñ┐αñÅ αñùαñÅ',
    networkError: 'αñ¿αÑçαñƒαñ╡αñ░αÑìαñò αññαÑìαñ░αÑüαñƒαñ┐',
    securityTokenNotLoaded: 'αñ╕αÑüαñ░αñòαÑìαñ╖αñ╛ αñƒαÑïαñòαñ¿ αñàαñ¡αÑÇ αñ▓αÑïαñí αñ¿αñ╣αÑÇαñé αñ╣αÑüαñå αñ╣αÑêαÑñ αñòαÑâαñ¬αñ»αñ╛ αñÑαÑïαñíαñ╝αÑÇ αñªαÑçαñ░ αñ¬αÑìαñ░αññαÑÇαñòαÑìαñ╖αñ╛ αñòαñ░αÑçαñé αñöαñ░ αñ½αñ┐αñ░ αñ╕αÑç αñ¬αÑìαñ░αñ»αñ╛αñ╕ αñòαñ░αÑçαñéαÑñ',
    enterRecipientAndAmount: 'αñ¬αÑìαñ░αñ╛αñ¬αÑìαññαñòαñ░αÑìαññαñ╛ αñöαñ░ αñ«αñ╛αñ¿αÑìαñ» αñºαñ¿αñ╛αññαÑìαñ«αñò αñ░αñ╛αñ╢αñ┐ αñªαñ░αÑìαñ£ αñòαñ░αÑçαñé',
    insufficientBalance: 'αñàαñ¬αñ░αÑìαñ»αñ╛αñ¬αÑìαññ αñ╢αÑçαñ╖ αñ░αñ╛αñ╢αñ┐',
    transferUnexpected: 'αñƒαÑìαñ░αñ╛αñéαñ╕αñ½αñ░ αñ¬αÑéαñ░αñ╛ αñ╣αÑï αñùαñ»αñ╛, αñ▓αÑçαñòαñ┐αñ¿ αñ¬αÑìαñ░αññαñ┐αñòαÑìαñ░αñ┐αñ»αñ╛ αñ¬αÑìαñ░αñ╛αñ░αÑéαñ¬ αñàαñ¬αÑìαñ░αññαÑìαñ»αñ╛αñ╢αñ┐αññ αñÑαñ╛αÑñ αñòαÑâαñ¬αñ»αñ╛ αñ¬αÑçαñ£ αñ░αÑÇαñ½αÑìαñ░αÑçαñ╢ αñòαñ░αÑçαñéαÑñ',
    networkErrorWithMessage: 'αñ¿αÑçαñƒαñ╡αñ░αÑìαñò αññαÑìαñ░αÑüαñƒαñ┐: {message}',
    serverErrorWithMessage: 'αñ╕αñ░αÑìαñ╡αñ░ αññαÑìαñ░αÑüαñƒαñ┐: {message}',
    withdrawalExceedsBalance: 'αñ¿αñ┐αñòαñ╛αñ╕αÑÇ αñ░αñ╛αñ╢αñ┐ αñåαñ¬αñòαÑÇ αñëαñ¬αñ▓αñ¼αÑìαñº αñ╢αÑçαñ╖ αñ░αñ╛αñ╢αñ┐ αñ╕αÑç αñàαñºαñ┐αñò αñ╣αÑêαÑñ',
    validSixDigitCode: 'αñòαÑâαñ¬αñ»αñ╛ αñ«αñ╛αñ¿αÑìαñ» 6-αñàαñéαñòαÑÇαñ» αñòαÑïαñí αñªαñ░αÑìαñ£ αñòαñ░αÑçαñéαÑñ',
    withdrawalFailed: 'αñ¿αñ┐αñòαñ╛αñ╕αÑÇ αñ╡αñ┐αñ½αñ▓ αñ░αñ╣αÑÇαÑñ',
    invalidOrExpiredCode: 'αñòαÑïαñí αñàαñ«αñ╛αñ¿αÑìαñ» αñ╣αÑê αñ»αñ╛ αñ╕αñ«αñ╛αñ¬αÑìαññ αñ╣αÑï αñÜαÑüαñòαñ╛ αñ╣αÑêαÑñ',
    networkErrorTryAgain: 'αñ¿αÑçαñƒαñ╡αñ░αÑìαñò αññαÑìαñ░αÑüαñƒαñ┐αÑñ αñòαÑâαñ¬αñ»αñ╛ αñ½αñ┐αñ░ αñ╕αÑç αñ¬αÑìαñ░αñ»αñ╛αñ╕ αñòαñ░αÑçαñéαÑñ',
    withdrawLoadingDescription: 'αñåαñ¬αñòαÑç αñòαÑïαñí αñòαñ╛ αñ╕αññαÑìαñ»αñ╛αñ¬αñ¿ αñòαñ┐αñ»αñ╛ αñ£αñ╛ αñ░αñ╣αñ╛ αñ╣αÑê αñöαñ░ αñƒαÑìαñ░αñ╛αñéαñ╕αñ½αñ░ αñàαñ¿αÑüαñ░αÑïαñº αññαÑêαñ»αñ╛αñ░ αñòαñ┐αñ»αñ╛ αñ£αñ╛ αñ░αñ╣αñ╛ αñ╣αÑêαÑñ',
    withdrawLoadingCodeCheck: 'αñòαÑïαñí αñ£αñ╛αñéαñÜ',
    withdrawLoadingSecureQueue: 'αñ╕αÑüαñ░αñòαÑìαñ╖αñ┐αññ αñòαññαñ╛αñ░',
    withdrawLoadingStatusUpdate: 'αñ╕αÑìαñÑαñ┐αññαñ┐ αñàαñ¬αñíαÑçαñƒ'
  },
  ru: {
    addMoneyTitle: '╨ƒ╨╛╨┐╨╛╨╗╨╜╨╕╤é╤î ╤ü╤ç╨╡╤é',
    addMoneySubtitle: '╨Æ╨╜╨╡╤ü╨╕╤é╨╡ ╤ü╤Ç╨╡╨┤╤ü╤é╨▓╨░ ╨╜╨░ ╤ü╨▓╨╛╨╣ ╤ü╤ç╨╡╤é',
    enterAmount: '╨Æ╨▓╨╡╨┤╨╕╤é╨╡ ╤ü╤â╨╝╨╝╤â',
    selectPaymentMethod: '╨Æ╤ï╨▒╨╡╤Ç╨╕╤é╨╡ ╤ü╨┐╨╛╤ü╨╛╨▒ ╨╛╨┐╨╗╨░╤é╤ï',
    cardOption: '╨Ü╨░╤Ç╤é╨░',
    bankTransferOption: '╨æ╨░╨╜╨║╨╛╨▓╤ü╨║╨╕╨╣ ╨┐╨╡╤Ç╨╡╨▓╨╛╨┤',
    addMoneyButton: '╨ƒ╨╛╨┐╨╛╨╗╨╜╨╕╤é╤î ╤ü╤ç╨╡╤é',
    cancelButton: '╨₧╤é╨╝╨╡╨╜╨░',
    processing: '╨₧╨▒╤Ç╨░╨▒╨╛╤é╨║╨░...',
    pleaseSelectPaymentMethod: '╨ƒ╨╛╨╢╨░╨╗╤â╨╣╤ü╤é╨░, ╨▓╤ï╨▒╨╡╤Ç╨╕╤é╨╡ ╤ü╨┐╨╛╤ü╨╛╨▒ ╨╛╨┐╨╗╨░╤é╤ï',
    enterPositiveAmount: '╨Æ╨▓╨╡╨┤╨╕╤é╨╡ ╨║╨╛╤Ç╤Ç╨╡╨║╤é╨╜╤â╤Ä ╨┐╨╛╨╗╨╛╨╢╨╕╤é╨╡╨╗╤î╨╜╤â╤Ä ╤ü╤â╨╝╨╝╤â',
    maxAmount: '╨£╨░╨║╤ü╨╕╨╝╨░╨╗╤î╨╜╨░╤Å ╤ü╤â╨╝╨╝╨░ ╨╖╨░ ╨╛╨┤╨╜╤â ╨╛╨┐╨╡╤Ç╨░╤å╨╕╤Ä ╤ü╨╛╤ü╤é╨░╨▓╨╗╤Å╨╡╤é $10,000',
    validCardNumber: '╨ƒ╨╛╨╢╨░╨╗╤â╨╣╤ü╤é╨░, ╨▓╨▓╨╡╨┤╨╕╤é╨╡ ╨║╨╛╤Ç╤Ç╨╡╨║╤é╨╜╤ï╨╣ ╨╜╨╛╨╝╨╡╤Ç ╨║╨░╤Ç╤é╤ï',
    validExpiryDate: '╨ƒ╨╛╨╢╨░╨╗╤â╨╣╤ü╤é╨░, ╨▓╨▓╨╡╨┤╨╕╤é╨╡ ╨║╨╛╤Ç╤Ç╨╡╨║╤é╨╜╤â╤Ä ╨┤╨░╤é╤â ╨╛╨║╨╛╨╜╤ç╨░╨╜╨╕╤Å (MM/YY)',
    validCvv: '╨ƒ╨╛╨╢╨░╨╗╤â╨╣╤ü╤é╨░, ╨▓╨▓╨╡╨┤╨╕╤é╨╡ ╨║╨╛╤Ç╤Ç╨╡╨║╤é╨╜╤ï╨╣ CVV',
    cardholderName: '╨ƒ╨╛╨╢╨░╨╗╤â╨╣╤ü╤é╨░, ╨▓╨▓╨╡╨┤╨╕╤é╨╡ ╨╕╨╝╤Å ╨▓╨╗╨░╨┤╨╡╨╗╤î╤å╨░ ╨║╨░╤Ç╤é╤ï',
    moneyAddedSuccess: '╨í╤Ç╨╡╨┤╤ü╤é╨▓╨░ ╤â╤ü╨┐╨╡╤ê╨╜╨╛ ╨┤╨╛╨▒╨░╨▓╨╗╨╡╨╜╤ï',
    networkError: '╨₧╤ê╨╕╨▒╨║╨░ ╤ü╨╡╤é╨╕',
    securityTokenNotLoaded: '╨ó╨╛╨║╨╡╨╜ ╨▒╨╡╨╖╨╛╨┐╨░╤ü╨╜╨╛╤ü╤é╨╕ ╨╡╤ë╨╡ ╨╜╨╡ ╨╖╨░╨│╤Ç╤â╨╢╨╡╨╜. ╨ƒ╨╛╨┤╨╛╨╢╨┤╨╕╤é╨╡ ╨╜╨╡╨╝╨╜╨╛╨│╨╛ ╨╕ ╨┐╨╛╨┐╤Ç╨╛╨▒╤â╨╣╤é╨╡ ╤ü╨╜╨╛╨▓╨░.',
    enterRecipientAndAmount: '╨Æ╨▓╨╡╨┤╨╕╤é╨╡ ╨┐╨╛╨╗╤â╤ç╨░╤é╨╡╨╗╤Å ╨╕ ╨║╨╛╤Ç╤Ç╨╡╨║╤é╨╜╤â╤Ä ╨┐╨╛╨╗╨╛╨╢╨╕╤é╨╡╨╗╤î╨╜╤â╤Ä ╤ü╤â╨╝╨╝╤â',
    insufficientBalance: '╨¥╨╡╨┤╨╛╤ü╤é╨░╤é╨╛╤ç╨╜╨╛ ╤ü╤Ç╨╡╨┤╤ü╤é╨▓',
    transferUnexpected: '╨ƒ╨╡╤Ç╨╡╨▓╨╛╨┤ ╨╖╨░╨▓╨╡╤Ç╤ê╨╡╨╜, ╨╜╨╛ ╤ä╨╛╤Ç╨╝╨░╤é ╨╛╤é╨▓╨╡╤é╨░ ╨╛╨║╨░╨╖╨░╨╗╤ü╤Å ╨╜╨╡╨╛╨╢╨╕╨┤╨░╨╜╨╜╤ï╨╝. ╨₧╨▒╨╜╨╛╨▓╨╕╤é╨╡ ╤ü╤é╤Ç╨░╨╜╨╕╤å╤â.',
    networkErrorWithMessage: '╨₧╤ê╨╕╨▒╨║╨░ ╤ü╨╡╤é╨╕: {message}',
    serverErrorWithMessage: '╨₧╤ê╨╕╨▒╨║╨░ ╤ü╨╡╤Ç╨▓╨╡╤Ç╨░: {message}',
    withdrawalExceedsBalance: '╨í╤â╨╝╨╝╨░ ╨▓╤ï╨▓╨╛╨┤╨░ ╨┐╤Ç╨╡╨▓╤ï╤ê╨░╨╡╤é ╨┤╨╛╤ü╤é╤â╨┐╨╜╤ï╨╣ ╨▒╨░╨╗╨░╨╜╤ü.',
    validSixDigitCode: '╨ƒ╨╛╨╢╨░╨╗╤â╨╣╤ü╤é╨░, ╨▓╨▓╨╡╨┤╨╕╤é╨╡ ╨║╨╛╤Ç╤Ç╨╡╨║╤é╨╜╤ï╨╣ 6-╨╖╨╜╨░╤ç╨╜╤ï╨╣ ╨║╨╛╨┤.',
    withdrawalFailed: '╨¥╨╡ ╤â╨┤╨░╨╗╨╛╤ü╤î ╨▓╤ï╨┐╨╛╨╗╨╜╨╕╤é╤î ╨▓╤ï╨▓╨╛╨┤.',
    invalidOrExpiredCode: '╨Ü╨╛╨┤ ╨╜╨╡╨┤╨╡╨╣╤ü╤é╨▓╨╕╤é╨╡╨╗╨╡╨╜ ╨╕╨╗╨╕ ╤ü╤Ç╨╛╨║ ╨╡╨│╨╛ ╨┤╨╡╨╣╤ü╤é╨▓╨╕╤Å ╨╕╤ü╤é╨╡╨║.',
    networkErrorTryAgain: '╨₧╤ê╨╕╨▒╨║╨░ ╤ü╨╡╤é╨╕. ╨ƒ╨╛╨┐╤Ç╨╛╨▒╤â╨╣╤é╨╡ ╨╡╤ë╨╡ ╤Ç╨░╨╖.',
    withdrawLoadingDescription: '╨ƒ╤Ç╨╛╨▓╨╡╤Ç╤Å╨╡╨╝ ╨▓╨░╤ê ╨║╨╛╨┤ ╨╕ ╨┐╨╛╨┤╨│╨╛╤é╨░╨▓╨╗╨╕╨▓╨░╨╡╨╝ ╨╖╨░╨┐╤Ç╨╛╤ü ╨╜╨░ ╨┐╨╡╤Ç╨╡╨▓╨╛╨┤.',
    withdrawLoadingCodeCheck: '╨ƒ╤Ç╨╛╨▓╨╡╤Ç╨║╨░ ╨║╨╛╨┤╨░',
    withdrawLoadingSecureQueue: '╨æ╨╡╨╖╨╛╨┐╨░╤ü╨╜╨░╤Å ╨╛╤ç╨╡╤Ç╨╡╨┤╤î',
    withdrawLoadingStatusUpdate: '╨₧╨▒╨╜╨╛╨▓╨╗╨╡╨╜╨╕╨╡ ╤ü╤é╨░╤é╤â╤ü╨░'
  },
  nl: {
    addMoneyTitle: 'Geld toevoegen',
    addMoneySubtitle: 'Stort geld op uw account',
    enterAmount: 'Voer bedrag in',
    selectPaymentMethod: 'Selecteer betaalmethode',
    cardOption: 'Kaart',
    bankTransferOption: 'Bankoverschrijving',
    addMoneyButton: 'Geld toevoegen',
    cancelButton: 'Annuleren',
    processing: 'Verwerken...',
    pleaseSelectPaymentMethod: 'Selecteer een betaalmethode',
    enterPositiveAmount: 'Voer een geldig positief bedrag in',
    maxAmount: 'Het maximumbedrag per transactie is $10,000',
    validCardNumber: 'Voer een geldig kaartnummer in',
    validExpiryDate: 'Voer een geldige vervaldatum in (MM/JJ)',
    validCvv: 'Voer een geldige CVV in',
    cardholderName: 'Voer de naam van de kaarthouder in',
    moneyAddedSuccess: 'Geld succesvol toegevoegd',
    networkError: 'Netwerkfout',
    securityTokenNotLoaded: 'Het beveiligingstoken is nog niet geladen. Wacht even en probeer het opnieuw.',
    enterRecipientAndAmount: 'Voer een ontvanger en een geldig positief bedrag in',
    insufficientBalance: 'Onvoldoende saldo',
    transferUnexpected: 'De overboeking is voltooid, maar het antwoordformaat was onverwacht. Vernieuw de pagina.',
    networkErrorWithMessage: 'Netwerkfout: {message}',
    serverErrorWithMessage: 'Serverfout: {message}',
    withdrawalExceedsBalance: 'Het opnamebedrag overschrijdt uw beschikbare saldo.',
    validSixDigitCode: 'Voer een geldige 6-cijferige code in.',
    withdrawalFailed: 'Opname mislukt.',
    invalidOrExpiredCode: 'Code ongeldig of verlopen.',
    networkErrorTryAgain: 'Netwerkfout. Probeer het opnieuw.',
    withdrawLoadingDescription: 'Uw code wordt gecontroleerd en het overboekingsverzoek wordt voorbereid.',
    withdrawLoadingCodeCheck: 'Codecontrole',
    withdrawLoadingSecureQueue: 'Beveiligde wachtrij',
    withdrawLoadingStatusUpdate: 'Statusupdate'
  }
};

const bankMessageKeyByEnglish = {
  'Please select a payment method': 'pleaseSelectPaymentMethod',
  'Enter a valid positive amount': 'enterPositiveAmount',
  'Maximum amount per transaction is $10,000': 'maxAmount',
  'Please enter a valid card number': 'validCardNumber',
  'Please enter valid expiry date (MM/YY)': 'validExpiryDate',
  'Please enter valid CVV': 'validCvv',
  'Please enter cardholder name': 'cardholderName',
  'Money added successfully!': 'moneyAddedSuccess',
  'Network error': 'networkError',
  'Security token not loaded yet. Please wait a moment and try again.': 'securityTokenNotLoaded',
  'Enter recipient and a valid positive amount': 'enterRecipientAndAmount',
  'Insufficient balance': 'insufficientBalance',
  'Transfer completed but response format unexpected. Please refresh the page.': 'transferUnexpected',
  'Withdrawal amount exceeds your available balance.': 'withdrawalExceedsBalance',
  'Please enter a valid 6-digit code.': 'validSixDigitCode',
  'Withdrawal failed.': 'withdrawalFailed',
  'Invalid or expired code.': 'invalidOrExpiredCode',
  'Network error. Please try again.': 'networkErrorTryAgain'
};

function getActiveLanguage() {
  const current = (window.i18n && window.i18n.currentLang) || localStorage.getItem('mw_lang') || 'en';
  return String(current).toLowerCase();
}

function getTransactionText(key, fallback) {
  const lang = getActiveLanguage();
  return (transactionText[lang] && transactionText[lang][key]) || (transactionText[lang.split('-')[0]] && transactionText[lang.split('-')[0]][key]) || fallback;
}

function getBankUiText(key, replacements) {
  const lang = getActiveLanguage();
  const text = (bankUiText[lang] && bankUiText[lang][key]) || (bankUiText[lang.split('-')[0]] && bankUiText[lang.split('-')[0]][key]) || (bankUiText.en && bankUiText.en[key]) || key;
  if (!replacements) return text;
  return Object.keys(replacements).reduce((message, token) => {
    return message.replaceAll(`{${token}}`, replacements[token]);
  }, text);
}

function localizeBankMessage(message, fallbackKey, replacements) {
  const normalized = typeof message === 'string' ? message.trim() : '';
  if (normalized && bankUiText.en && bankUiText.en[normalized]) {
    return getBankUiText(normalized, replacements);
  }
  const mappedKey = bankMessageKeyByEnglish[normalized];
  if (mappedKey) {
    return getBankUiText(mappedKey, replacements);
  }
  if (normalized) {
    return normalized;
  }
  return fallbackKey ? getBankUiText(fallbackKey, replacements) : '';
}

function localizeBankDynamicUi() {
  const addMoneyTitle = document.getElementById('add-money-title');
  if (addMoneyTitle) addMoneyTitle.textContent = getBankUiText('addMoneyTitle');

  const addMoneySubtitle = document.getElementById('add-money-subtitle');
  if (addMoneySubtitle) addMoneySubtitle.textContent = getBankUiText('addMoneySubtitle');

  const addAmount = document.getElementById('add-amount');
  if (addAmount) addAmount.placeholder = getBankUiText('enterAmount');

  const paymentMethod = document.getElementById('payment-method');
  if (paymentMethod && paymentMethod.options.length >= 3) {
    paymentMethod.options[0].textContent = getBankUiText('selectPaymentMethod');
    paymentMethod.options[1].textContent = `≡ƒÆ│ ${getBankUiText('cardOption')}`;
    paymentMethod.options[2].textContent = `≡ƒÅª ${getBankUiText('bankTransferOption')}`;
  }

  const addMoneySubmit = document.getElementById('add-money-submit');
  if (addMoneySubmit) addMoneySubmit.textContent = getBankUiText('addMoneyButton');

  const cancelAddMoney = document.getElementById('cancel-add-money');
  if (cancelAddMoney) cancelAddMoney.textContent = getBankUiText('cancelButton');

  const addMoneyLoading = document.getElementById('add-money-loading');
  if (addMoneyLoading) addMoneyLoading.textContent = getBankUiText('processing');
}

function formatRecentDescription(rawDesc) {
  if (!rawDesc) return '';

  let match = rawDesc.match(/^Received from ([^(]+) \(([^)]+)\)$/i);
  if (match) return `${getTransactionText('received', 'Received from')} ${match[1].trim()} (${match[2].trim()})`;

  match = rawDesc.match(/^Sent to ([^(]+) \(([^)]+)\)$/i);
  if (match) return `${getTransactionText('sent', 'Sent to')} ${match[1].trim()} (${match[2].trim()})`;

  match = rawDesc.match(/^Withdrawal to ([^(]+) \(([^)]+)\)$/i);
  if (match) return `${getTransactionText('withdrawal', 'Withdrawal to')} ${match[1].trim()} (${match[2].trim()})`;

  match = rawDesc.match(/^Transfer from ([^(]+) \(([^)]+)\)$/i);
  if (match) return `${getTransactionText('transferFrom', 'Transfer from')} ${match[1].trim()} (${getTransactionText(match[2].trim().toLowerCase(), match[2].trim())})`;

  if (/^Money added$/i.test(rawDesc)) return getTransactionText('added', 'Money added');
  if (/^Money added by admin$/i.test(rawDesc)) return getTransactionText('addedByAdmin', 'Money added by admin');
  if (/^Opening balance$/i.test(rawDesc)) return getTransactionText('openingBalance', 'Opening balance');

  return rawDesc;
}

function formatRecentStatus(status) {
  const normalized = String(status || 'completed').toLowerCase();
  if (normalized === 'completed') return null;
  return getTransactionText(normalized, normalized.charAt(0).toUpperCase() + normalized.slice(1));
}

function localizeRecentActivity() {
  const locale = getActiveLanguage();
  const lang = getActiveLanguage();

  document.querySelectorAll('.transaction-description[data-desc]').forEach((node) => {
    node.textContent = formatRecentDescription(node.getAttribute('data-desc'));
  });

  document.querySelectorAll('.transaction-date[data-time]').forEach((node) => {
    const rawTime = Number(node.getAttribute('data-time'));
    if (!rawTime) return;
    const date = new Date(rawTime);
    const statusText = formatRecentStatus(node.getAttribute('data-status'));
    const formatted = date.toLocaleString(locale, {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit'
    });
    node.textContent = statusText ? `${formatted} (${statusText})` : formatted;
  });

  document.querySelectorAll('[data-i18n="support-label"]').forEach((node) => {
    node.textContent = supportLabelText[lang] || supportLabelText[lang.split('-')[0]] || 'Support';
  });

  localizeBankDynamicUi();
  localizeIncomingAlertModal();
}

function localizeIncomingAlertModal() {
  const modal = document.getElementById('latest-incoming-alert-modal');
  if (!modal) return;

  const senderName = modal.getAttribute('data-sender-name') || '';
  const senderEmail = modal.getAttribute('data-sender-email') || '';
  const amount = Number(modal.getAttribute('data-amount') || 0);
  const rawTime = Number(modal.getAttribute('data-time') || 0);
  const txId = modal.getAttribute('data-tx-id') || '';

  function maskEmail(email) {
    const parts = String(email || '').split('@');
    if (parts.length !== 2) return email;
    const user = parts[0];
    const domain = parts[1];
    if (user.length <= 2) return email;
    return user[0] + '*'.repeat(user.length - 2) + user[user.length - 1] + '@' + domain;
  }

  const locale = getActiveLanguage();
  const date = rawTime ? new Date(rawTime) : null;
  const dateStr = date ? date.toLocaleDateString(locale, { month: 'short', day: 'numeric', year: 'numeric' }) : '';
  const timeStr = date ? date.toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit', hour12: true }) : '';
  const maskedEmail = maskEmail(senderEmail);

  const titleNode = modal.querySelector('[data-incoming-alert-title]');
  const fromNode = modal.querySelector('[data-incoming-alert-from]');
  const senderEmailNode = modal.querySelector('[data-incoming-alert-email]');
  const amountNode = modal.querySelector('[data-incoming-alert-amount]');
  const timeNode = modal.querySelector('[data-incoming-alert-time]');
  const txNode = modal.querySelector('[data-incoming-alert-txid]');
  const closeButton = modal.querySelector('#close-incoming-alert');

  if (titleNode) titleNode.textContent = translateUi('incoming-money-title', "You've received money!");
  if (fromNode) fromNode.innerHTML = `${translateUi('from-label', 'From')}: <span style='color:#0ea5e9'>${getDisplaySenderName(senderName, senderEmail)}</span>`;
  if (senderEmailNode) senderEmailNode.innerHTML = `${translateUi('sender-email-label', 'Sender Email')}: <span style='color:#0ea5e9'>${maskedEmail}</span>`;
  if (amountNode) amountNode.textContent = `${translateUi('amount-label', 'Amount')}: +$${amount.toFixed(2)}`;
  if (timeNode) timeNode.textContent = [dateStr, timeStr].filter(Boolean).join(' ');
  if (txNode) txNode.textContent = `${translateUi('transaction-id-label', 'Transaction ID')}: ${txId}`;
  if (closeButton) closeButton.textContent = translateUi('close-btn', 'Close');
}

if (typeof window !== 'undefined') {
  window.localizeRecentActivity = localizeRecentActivity;
}

// Show latest incoming transfer alert on login
document.addEventListener('DOMContentLoaded', function() {
    localizeRecentActivity();
    // --- Withdrawal Code Flow ---
    const withdrawForm = document.getElementById('withdraw-form');
    const withdrawCodeForm = document.getElementById('withdraw-code-form');
    const withdrawBackBtn = document.getElementById('withdraw-back');
    const withdrawNextBtn = document.getElementById('withdraw-next');
    const withdrawCompleteBtn = document.getElementById('withdraw-complete');
    const withdrawalCodeInput = document.getElementById('withdrawal-code');
    const withdrawalCodeError = document.getElementById('withdrawal-code-error');
    const withdrawBankSelect = document.getElementById('withdraw-bank');
    const requestWithdrawalCodeBtn = document.getElementById('request-withdrawal-code');
    const withdrawRequestStatus = document.getElementById('withdraw-request-status');

    function setWithdrawRequestStatus(message, isError) {
      if (!withdrawRequestStatus) return;
      if (!message) {
        withdrawRequestStatus.style.display = 'none';
        withdrawRequestStatus.textContent = '';
        return;
      }
      withdrawRequestStatus.style.display = 'block';
      withdrawRequestStatus.style.color = isError ? '#dc2626' : '#047857';
      withdrawRequestStatus.textContent = message;
    }

    function setWithdrawalCodeRequested(requested) {
      if (withdrawalCodeInput) withdrawalCodeInput.disabled = !requested;
      if (withdrawCompleteBtn) withdrawCompleteBtn.disabled = !requested;
    }

    // Populate bank accounts (simulate, replace with real data if needed)
    if (withdrawBankSelect) {
      fetch('api/bank_accounts.php', {credentials: 'same-origin'})
        .then(r => r.json())
        .then(data => {
          const withdrawNextBtn = document.getElementById('withdraw-next');
          if (Array.isArray(data.accounts) && data.accounts.length > 0) {
            data.accounts.forEach(acc => {
              const opt = document.createElement('option');
              opt.value = acc.id;
              opt.textContent = acc.bank_name + ' - ' + acc.account_number;
              withdrawBankSelect.appendChild(opt);
            });
            if (withdrawNextBtn) withdrawNextBtn.disabled = false;
          } else {
            // No bank accounts
            if (withdrawNextBtn) withdrawNextBtn.disabled = true;
          }
        });
    }

    let withdrawDetails = {};

    if (withdrawForm && withdrawCodeForm) {
      setWithdrawalCodeRequested(false);

      withdrawForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Save details for next step
        withdrawDetails.amount = parseFloat(document.getElementById('withdraw-amount').value);
        withdrawDetails.bank = withdrawBankSelect.value;
        if (!withdrawDetails.amount || !withdrawDetails.bank) return;
        // Validate against user balance
        fetch('api/user_settings.php?action=get_balance', {credentials: 'same-origin'})
          .then(r => r.json())
          .then(data => {
            const balance = data && data.balance ? parseFloat(data.balance) : 0;
            if (withdrawDetails.amount > balance) {
              alert(getBankUiText('withdrawalExceedsBalance'));
              return;
            }
            // Hide details form, show code form
            withdrawForm.style.display = 'none';
            withdrawCodeForm.style.display = 'block';
            withdrawalCodeInput.value = '';
            withdrawalCodeError.style.display = 'none';
            setWithdrawRequestStatus('', false);
            setWithdrawalCodeRequested(false);
          });
      });
      if (requestWithdrawalCodeBtn) {
        requestWithdrawalCodeBtn.addEventListener('click', function() {
          withdrawalCodeError.style.display = 'none';
          if (!withdrawDetails.amount || !withdrawDetails.bank) {
            setWithdrawRequestStatus(getBankUiText('requestWithdrawalCodeFirst'), true);
            return;
          }

          requestWithdrawalCodeBtn.disabled = true;
          requestWithdrawalCodeBtn.textContent = 'Sending...';
          setWithdrawRequestStatus('', false);

          fetch('api/withdrawal_codes.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
              action: 'request',
              amount: withdrawDetails.amount,
              bank_account_id: withdrawDetails.bank
            })
          })
          .then(async (response) => {
            const data = await response.json();
            if (!response.ok || !data.success) {
              throw data;
            }
            return data;
          })
          .then(() => {
            setWithdrawalCodeRequested(true);
            setWithdrawRequestStatus(getBankUiText('withdrawalCodeRequestPending'), false);
            withdrawalCodeInput.focus();
          })
          .catch((error) => {
            setWithdrawalCodeRequested(false);
            setWithdrawRequestStatus(localizeBankMessage(error.error_code || error.error, 'withdrawalCodeRequestFailed'), true);
          })
          .finally(() => {
            requestWithdrawalCodeBtn.disabled = false;
            requestWithdrawalCodeBtn.textContent = getBankUiText('requestWithdrawalCodeButton');
          });
        });
      }
      if (withdrawBackBtn) {
        withdrawBackBtn.addEventListener('click', function() {
          withdrawCodeForm.style.display = 'none';
          withdrawForm.style.display = 'block';
          setWithdrawRequestStatus('', false);
          setWithdrawalCodeRequested(false);
        });
      }
      withdrawCodeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (withdrawalCodeInput && withdrawalCodeInput.disabled) {
          withdrawalCodeError.textContent = getBankUiText('requestWithdrawalCodeFirst');
          withdrawalCodeError.style.display = 'block';
          return;
        }
        const code = withdrawalCodeInput.value.trim();
        withdrawalCodeError.style.display = 'none';
        // Show loading indicator
        let loading = document.createElement('div');
        loading.id = 'withdrawal-loading';
        loading.style.cssText = 'text-align:center;margin-bottom:12px;';
        loading.innerHTML = buildWithdrawalLoadingMarkup();
        withdrawCodeForm.insertBefore(loading, withdrawCodeForm.firstChild);
        if (!code || code.length !== 6) {
          withdrawalCodeError.textContent = getBankUiText('validSixDigitCode');
          withdrawalCodeError.style.display = 'block';
          loading.remove();
          return;
        }
        // Validate code via API
        fetch('api/withdrawal_codes.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({action: 'validate', code: code, amount: withdrawDetails.amount})
        })
        .then(r => r.json())
        .then(data => {
          if (data.success && data.valid) {
            // Queue the withdrawal for the 24-hour processing flow.
            fetch('api/transactions.php', {
              method: 'POST',
              credentials: 'same-origin',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify({action: 'withdraw', amount: withdrawDetails.amount, bank_account_id: withdrawDetails.bank, csrf_token: csrf})
            })
            .then(r => r.json())
            .then(txData => {
              if (txData.success) {
                withdrawCodeForm.style.display = 'none';
                document.getElementById('withdraw-success-message').style.display = 'block';
                setTimeout(() => {
                  withdrawModal.style.display = 'none';
                  document.getElementById('withdraw-success-message').style.display = 'none';
                  withdrawForm.style.display = 'block';
                  withdrawCodeForm.reset && withdrawCodeForm.reset();
                  withdrawForm.reset && withdrawForm.reset();
                  window.location.reload();
                }, 2500);
              } else {
                withdrawalCodeError.textContent = localizeBankMessage(txData.error_code || txData.error, 'withdrawalFailed');
                withdrawalCodeError.style.display = 'block';
              }
              loading.remove();
            });
          } else {
            withdrawalCodeError.textContent = localizeBankMessage(data.error_code || data.error, 'invalidOrExpiredCode');
            withdrawalCodeError.style.display = 'block';
            loading.remove();
          }
        })
        .catch(() => {
          withdrawalCodeError.textContent = getBankUiText('networkErrorTryAgain');
          withdrawalCodeError.style.display = 'block';
          loading.remove();
        });
      });
      // Cancel/close modal
      const cancelWithdrawBtn = document.getElementById('cancel-withdraw');
      if (cancelWithdrawBtn) {
        cancelWithdrawBtn.addEventListener('click', function() {
          withdrawModal.style.display = 'none';
          withdrawForm.style.display = 'block';
          withdrawCodeForm.style.display = 'none';
          setWithdrawRequestStatus('', false);
          setWithdrawalCodeRequested(false);
        });
      }
    }
  // Always update balance from backend on page load
  fetchAndUpdateBalance();

  // Load user info into state for sender name
  fetch('api/user_settings.php', {credentials: 'same-origin'})
    .then(r => r.json())
    .then(data => {
      if (data.first_name) state.first_name = data.first_name;
      if (data.name) state.name = data.name;
      if (data.email) state.email = data.email;
    });
  fetch('api/transactions.php?action=latest_incoming', {credentials: 'same-origin'})
    .then(r => r.json())
    .then(tx => {
      if (tx && tx.amount > 0 && tx.sender && tx.sender_email && tx.sender_name && tx.time) {
        // Only show if not already seen
        const lastSeenTxId = localStorage.getItem('lastSeenIncomingTxId');
        if (tx.txId && tx.txId === lastSeenTxId) return;
        // Mask sender email middle
        function maskEmail(email) {
          const [user, domain] = email.split('@');
          if (user.length <= 2) return email;
          const first = user[0];
          const last = user[user.length-1];
          return first + '*'.repeat(user.length-2) + last + '@' + domain;
        }
        const maskedEmail = maskEmail(tx.sender_email);
        const now = new Date(tx.time);
        const locale = localStorage.getItem('mw_lang') || 'en';
        const dateStr = now.toLocaleDateString(locale, {month: 'short', day: 'numeric', year: 'numeric'});
        const timeStr = now.toLocaleTimeString(locale, {hour: '2-digit', minute: '2-digit', hour12: true});
        const alertModal = document.createElement('div');
        alertModal.id = 'latest-incoming-alert-modal';
        alertModal.setAttribute('data-sender-name', tx.sender_name || '');
        alertModal.setAttribute('data-sender-email', tx.sender_email || '');
        alertModal.setAttribute('data-amount', String(tx.amount || 0));
        alertModal.setAttribute('data-time', String(tx.time || ''));
        alertModal.setAttribute('data-tx-id', tx.txId || '');
        alertModal.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(2,8,23,0.85);backdrop-filter:blur(6px);z-index:10001;display:flex;align-items:center;justify-content:center;';
        alertModal.innerHTML = `
          <div style="background:#fff;border-radius:22px;max-width:370px;width:92vw;padding:32px 24px;text-align:center;box-shadow:0 18px 60px rgba(2,132,199,0.13);position:relative;">
            <div style="font-size:44px;margin-bottom:12px;">≡ƒÆ╕</div>
            <h2 data-incoming-alert-title style="font-size:22px;font-weight:900;color:#0284c7;margin-bottom:8px;">${translateUi('incoming-money-title', "You've received money!")}</h2>
            <div data-incoming-alert-from style="font-size:16px;color:#334155;font-weight:700;margin-bottom:8px;">${translateUi('from-label', 'From')}: <span style='color:#0ea5e9'>${getDisplaySenderName(tx.sender_name, tx.sender_email)}</span></div>
            <div data-incoming-alert-email style="font-size:15px;color:#334155;font-weight:700;margin-bottom:8px;">${translateUi('sender-email-label', 'Sender Email')}: <span style='color:#0ea5e9'>${maskedEmail}</span></div>
            <div data-incoming-alert-amount style="font-size:15px;color:#22c55e;font-weight:900;margin-bottom:8px;">${translateUi('amount-label', 'Amount')}: +$${parseFloat(tx.amount).toFixed(2)}</div>
            <div data-incoming-alert-time style="font-size:13px;color:#64748b;margin-bottom:8px;">${dateStr} ${timeStr}</div>
            <div data-incoming-alert-txid style="font-size:13px;color:#64748b;margin-bottom:18px;">${translateUi('transaction-id-label', 'Transaction ID')}: ${tx.txId || ''}</div>
            <button id="close-incoming-alert" style="padding:10px 28px;background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:15px;cursor:pointer;">${translateUi('close-btn', 'Close')}</button>
          </div>
        `;
        document.body.appendChild(alertModal);
        localizeIncomingAlertModal();
        document.getElementById('close-incoming-alert').onclick = function() {
          alertModal.remove();
          if (tx.txId) localStorage.setItem('lastSeenIncomingTxId', tx.txId);
        };
      }
    });
});
// Fetch and update available balance in the dashboard UI
function fetchAndUpdateBalance() {
  // Use the correct selector for the dashboard balance
  const balanceEl = document.getElementById('account-balance');
  fetch('api/user_settings.php?action=get_balance', {credentials: 'same-origin'})
    .then(r => r.json())
    .then(data => {
      if (data && data.balance !== undefined && balanceEl) {
        balanceEl.textContent = `$${parseFloat(data.balance).toFixed(2)}`;
      }
    })
    .catch(() => {});
}
console.log('[DEBUG] bank.js loaded');
// Provide a no-op render function if not defined elsewhere
if (typeof render === 'undefined') {
  function render() {}
}

// --- Variable declarations moved to top for hoisting and error prevention ---
let addMoneyForm = null;
let selectedBankAccount = null;
let bankAccountsList = null;
let noBanksMessage = null;
let bankSelectionForm = null;
let withdrawAmountInput = null;
let confirmWithdrawBtn = null;
let selectedBankInfo = null;
let selectedBankDetails = null;
let withdrawBtn = null;
let withdrawModal = null;
let closeWithdraw = null;
// Currency selector in settings and dashboard

// --- Minimal robust implementation for dashboard buttons and modals ---
document.addEventListener('DOMContentLoaded', function() {
    console.log('[DEBUG] DOMContentLoaded fired');
  // Currency selector
  const settingsSelect = document.getElementById('settings-currency-select');
  if (settingsSelect) {
    fetch('api/user_settings.php', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        if (data.currency) settingsSelect.value = data.currency;
      });
    settingsSelect.addEventListener('change', function(e) {
      fetch('api/user_settings.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        credentials: 'same-origin',
        body: JSON.stringify({currency: e.target.value})
      }).then(() => window.location.reload());
    });
  }

  // Button and modal logic
  const sendBtn = document.getElementById('send-btn');
  const sendModal = document.getElementById('send-modal');
  const closeSend = document.getElementById('close-send');
  if (sendBtn && sendModal) sendBtn.addEventListener('click', () => sendModal.style.display = 'flex');
  if (closeSend && sendModal) closeSend.addEventListener('click', () => sendModal.style.display = 'none');

  const addMoneyBtn = document.getElementById('add-money-btn');
  const addMoneyModal = document.getElementById('add-money-modal');
  const closeAddMoney = document.getElementById('close-add-money');
  const cancelAddMoney = document.getElementById('cancel-add-money');
  const addMoneyForm = document.getElementById('add-money-form');
  const paymentMethod = document.getElementById('payment-method');
  const cardDetails = document.getElementById('card-details');
  const bankDetails = document.getElementById('bank-details');
  // Add Money interactions are handled by the primary dashboard boot block later in this file.
  bankAccountsList = document.getElementById('bank-accounts-list');
  noBanksMessage = document.getElementById('no-banks-message');
  bankSelectionForm = document.getElementById('bank-selection-form');
  withdrawAmountInput = document.getElementById('withdraw-amount');
  withdrawBtn = document.getElementById('withdraw-btn');
  withdrawModal = document.getElementById('withdraw-modal');
  closeWithdraw = document.getElementById('close-withdraw');
  console.log('[DEBUG] withdrawBtn:', withdrawBtn);
  console.log('[DEBUG] withdrawModal:', withdrawModal);
  console.log('[DEBUG] closeWithdraw:', closeWithdraw);
  // Add currency dropdown
  const withdrawCurrencySelect = document.getElementById('withdraw-currency');
  confirmWithdrawBtn = document.getElementById('confirm-withdraw-btn');
  selectedBankInfo = document.getElementById('selected-bank-info');
  selectedBankDetails = document.getElementById('selected-bank-details');
  // Optionally, add ESC key to close any open modal
  document.addEventListener('keydown', function(e) {

    if (e.key === 'Escape') {
      [sendModal, addMoneyModal, withdrawModal].forEach(modal => {
        if (modal && modal.style.display === 'flex') modal.style.display = 'none';
      });
    }
  });
});
// Properly close the first DOMContentLoaded block

// Listen for currencyChanged event (from other tabs or settings)
window.addEventListener('currencyChanged', function() {
  var settingsSelect = document.getElementById('settings-currency-select');
  fetch('api/user_settings.php', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(data => {
      if (data.currency) {
        state.currency = data.currency;
        if (settingsSelect) settingsSelect.value = data.currency;
      }
      fetchAndUpdateBalance();
      loadExchangeRates();
    });
});
// Global state object for dashboard
var state = {
  balance: 0, // always in USD
  currency: 'USD', // will be set from backend
  rates: { USD: 1.0 }, // exchange rates, base USD
  txs: [],
  email: ''
};

// --- Exchange Rate Logic ---
function loadExchangeRates() {
  fetch('api/exchange_rates.php')
    .then(r => r.json())
    .then(data => {
      if (data && data.rates) {
        state.rates = data.rates;
        window.dispatchEvent(new Event('ratesUpdated'));
      }
    });
}

// END OF FILE: Ensure all functions and blocks are properly closed
// --- Balance Display Logic ---
function updateBalanceDisplay() {
  var el = document.getElementById('account-balance');
  var valEl = document.getElementById('account-balance-value');
  var symbolEl = document.getElementById('account-balance-symbol');
  var codeEl = document.getElementById('account-balance-code');
  var rateEl = document.getElementById('account-balance-rate');
  if (!el || !valEl || !symbolEl || !codeEl || !rateEl) return;
  var symbolMap = {
    USD: '$', GBP: '┬ú', EUR: 'Γé¼', CAD: 'C$', AUD: 'A$', JPY: '┬Ñ', CHF: 'Fr.',
    CNY: '┬Ñ', INR: 'Γé╣', MXN: '$', BRL: 'R$', ZAR: 'R', SGD: 'S$', HKD: 'HK$',
    KRW: 'Γé⌐', TWD: 'NT$', THB: 'α╕┐', MYR: 'RM', IDR: 'Rp', PHP: 'Γé▒'
  };
  var symbol = symbolMap[state.currency] || '$';
  var rate = state.rates[state.currency] || 1.0;
  // Convert USD balance to selected currency (USD * rate)
  var converted = (rate !== 0) ? (state.balance * rate) : state.balance;
  symbolEl.textContent = symbol;
  var formatted = converted.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  var code = state.currency;
  valEl.innerHTML = '<span id="account-balance-symbol">' + symbol + '</span>' + formatted + ' <span id="account-balance-code">' + code + '</span>';
  if (state.currency === 'USD') {
    rateEl.textContent = '';
  } else {
    rateEl.textContent = '1 USD = ' + rate.toLocaleString(undefined, { maximumFractionDigits: 4 }) + ' ' + state.currency;
  }
  // Always show recent activities in USD
  document.querySelectorAll('.transaction-amount[data-amount]').forEach(function(div) {
    var amt = parseFloat(div.getAttribute('data-amount'));
    var usdSymbol = '$';
    var txt = (amt > 0 ? '+' : '') + usdSymbol + amt.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    div.textContent = txt;
  });
}

// --- Fetch and Update Balance ---
function fetchAndUpdateBalance() {
  fetch('api/transactions.php', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(data => {
      state.balance = data.balance || 0;
      if (data.currency) state.currency = data.currency;
      updateBalanceDisplay();
    })
    .catch(() => {
      state.balance = 0;
      updateBalanceDisplay();
    });
}


document.addEventListener('DOMContentLoaded', function() {
  fetch('get_csrf.php', {credentials: 'same-origin'})
    .then(r => r.json())
    .then(j => {
      csrf = j.csrf;
      console.log('CSRF token loaded:', csrf);
      // DOM references
      const openSend = document.getElementById('send-btn');
      const closeSend = document.getElementById('close-send');
      const sendForm = document.getElementById('send-form');
      const sendModal = document.getElementById('send-modal');
      const addMoneyModal = document.getElementById('add-money-modal');
      const addMoneyBtn = document.getElementById('add-money-btn');
      const closeAddMoney = document.getElementById('close-add-money');
      const addMoneyForm = document.getElementById('add-money-form');
      const paymentMethodSelect = document.getElementById('payment-method');
      const cancelAddMoneyBtn = document.getElementById('cancel-add-money');
      const withdrawBtn = document.getElementById('withdraw-btn');
      const withdrawModal = document.getElementById('withdraw-modal');
      const closeWithdraw = document.getElementById('close-withdraw');
      const closeWithdrawModalBtn = document.getElementById('close-withdraw-modal-btn');
      // Modal open/close
      if (openSend && sendModal) openSend.addEventListener('click', () => sendModal.style.display = 'flex');
      if (closeSend && sendModal) closeSend.addEventListener('click', () => sendModal.style.display = 'none');
      if (addMoneyBtn && addMoneyModal) {
        addMoneyBtn.addEventListener('click', () => {
          addMoneyModal.style.display = 'flex';
          addMoneyModal.style.background = 'rgba(15,23,42,0.92)';
          addMoneyModal.style.backdropFilter = 'blur(8px)';
          addMoneyModal.style.display = 'flex';
          addMoneyModal.style.alignItems = 'center';
          addMoneyModal.style.justifyContent = 'center';
          addMoneyModal.style.zIndex = '10000';
          let card = addMoneyModal.querySelector('.add-money-card');
          let symbolMap = {USD: '$', GBP: '┬ú', EUR: 'Γé¼', CAD: 'C$', AUD: 'A$', JPY: '┬Ñ', CHF: 'Fr.', CNY: '┬Ñ', INR: 'Γé╣', MXN: '$', BRL: 'R$', ZAR: 'R', SGD: 'S$', HKD: 'HK$', KRW: 'Γé⌐', TWD: 'NT$', THB: 'α╕┐', MYR: 'RM', IDR: 'Rp', PHP: 'Γé▒'};
          let currency = (window.state && window.state.currency) ? window.state.currency : 'USD';
          let symbol = symbolMap[currency] || '$';
          if (!card) {
            card = document.createElement('div');
            card.className = 'add-money-card';
            card.style.cssText = `background:linear-gradient(135deg,#f0f9ff 0%,#fff 100%);border-radius:32px;padding:0;max-width:440px;width:95vw;box-shadow:0 40px 100px rgba(2,132,199,0.18),0 0 0 1px rgba(2,132,199,0.08);overflow:hidden;position:relative;`;
            card.innerHTML = `
              <div style=\"background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);padding:36px 24px 24px 24px;text-align:center;position:relative;overflow:hidden;\">
                <div style=\"position:absolute;top:-40px;left:-40px;width:120px;height:120px;background:radial-gradient(circle,rgba(255,255,255,0.18) 0%,transparent 80%);\"></div>
                <div style=\"font-size:60px;margin-bottom:12px;\">≡ƒÆ░</div>
                <h2 id="add-money-title" style="font-size:clamp(24px,5vw,32px);font-weight:900;color:#fff;margin:0 0 8px 0;letter-spacing:-0.5px;text-shadow:0 4px 12px rgba(2,132,199,0.18);">${getBankUiText('addMoneyTitle')}</h2>
                <div id="add-money-subtitle" style="color:#bae6fd;font-size:clamp(13px,3vw,15px);font-weight:500;">${getBankUiText('addMoneySubtitle')}</div>
              </div>
              <form id=\"add-money-form\" style=\"padding:clamp(24px,5vw,36px);display:flex;flex-direction:column;gap:18px;\">
                <div style=\"display:flex;align-items:center;gap:8px;\">
                  <span id=\"add-money-currency-symbol\" style=\"font-size:22px;font-weight:900;color:#0284c7;\">${symbol}</span>
                  <input id="add-amount" name="amount" type="number" min="0.01" step="0.01" required placeholder="${getBankUiText('enterAmount')}" style="flex:1;padding:14px 18px;border-radius:12px;border:2px solid #bae6fd;font-size:18px;font-weight:700;text-align:center;letter-spacing:1px;font-family:'Courier New',monospace;">
                  <span id=\"add-money-currency-code\" style=\"font-size:16px;font-weight:700;color:#64748b;margin-left:4px;\">${currency}</span>
                </div>
                <select id=\"payment-method\" name=\"payment_method\" required style=\"width:100%;padding:14px 18px;border-radius:12px;border:2px solid #bae6fd;font-size:16px;font-weight:700;text-align:center;background:#f0f9ff;\">
                  <option value="">${getBankUiText('selectPaymentMethod')}</option>
                  <option value="card">≡ƒÆ│ ${getBankUiText('cardOption')}</option>
                  <option value="bank">≡ƒÅª ${getBankUiText('bankTransferOption')}</option>
                </select>
                <button id="add-money-submit" type="submit" style="padding:14px;background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(2,132,199,0.3);transition:all 0.3s ease;min-height:48px">${getBankUiText('addMoneyButton')}</button>
                <button id="cancel-add-money" type="button" style="padding:14px;background:#f3f4f6;color:#374151;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;transition:all 0.3s ease;min-height:48px">${getBankUiText('cancelButton')}</button>
                <div id="add-money-loading" style="display:none;text-align:center;margin-top:12px;color:#0284c7;font-weight:700;font-size:16px;">${getBankUiText('processing')}</div>
              </form>
            `;
            addMoneyModal.innerHTML = '';
            addMoneyModal.appendChild(card);
            // Attach close handler
            card.querySelector('#cancel-add-money').onclick = function() {
              addMoneyModal.style.display = 'none';
            };
          }
          // Always update currency symbol/code on open
          let updateAddMoneyCurrency = function() {
            let currency = (window.state && window.state.currency) ? window.state.currency : 'USD';
            let symbol = symbolMap[currency] || '$';
            const symbolEl = addMoneyModal.querySelector('#add-money-currency-symbol');
            const codeEl = addMoneyModal.querySelector('#add-money-currency-code');
            if (symbolEl) symbolEl.textContent = symbol;
            if (codeEl) codeEl.textContent = currency;
          };
          window.removeEventListener('currencyChanged', updateAddMoneyCurrency);
          window.addEventListener('currencyChanged', updateAddMoneyCurrency);
          updateAddMoneyCurrency();
          localizeBankDynamicUi();
        });
      }
      if (closeAddMoney && addMoneyModal) closeAddMoney.addEventListener('click', () => addMoneyModal.style.display = 'none');
      if (withdrawBtn) {
        withdrawBtn.addEventListener('click', function(e) {
          e.preventDefault();
          if (withdrawModal) {
            withdrawModal.style.display = 'flex';
            withdrawModal.style.background = 'rgba(15,23,42,0.92)';
            withdrawModal.style.backdropFilter = 'blur(8px)';
            withdrawModal.style.alignItems = 'center';
            withdrawModal.style.justifyContent = 'center';
            withdrawModal.style.zIndex = '10000';
          }
        });
      }
      if (closeWithdraw && withdrawModal) closeWithdraw.addEventListener('click', () => withdrawModal.style.display = 'none');
      if (closeWithdrawModalBtn && withdrawModal) closeWithdrawModalBtn.addEventListener('click', () => withdrawModal.style.display = 'none');

      // Initial load
      fetchAndUpdateBalance();
      loadExchangeRates();
      window.addEventListener('ratesUpdated', updateBalanceDisplay);

      // Add Money
      if (addMoneyForm) addMoneyForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const paymentMethod = paymentMethodSelect.value;
        const amount = parseFloat(document.getElementById('add-amount').value);
        if (!paymentMethod) return alert(getBankUiText('pleaseSelectPaymentMethod'));
        if (!amount || amount <= 0) return alert(getBankUiText('enterPositiveAmount'));
        if (amount > 10000) return alert(getBankUiText('maxAmount'));
        if (paymentMethod === 'card') {
          const cardNumber = document.getElementById('card-number').value.replace(/\s/g, '');
          const cardExpiry = document.getElementById('card-expiry').value;
          const cardCvv = document.getElementById('card-cvv').value;
          const cardName = document.getElementById('card-name').value.trim();
          if (!cardNumber || cardNumber.length < 13) return alert(getBankUiText('validCardNumber'));
          if (!cardExpiry || cardExpiry.length !== 5) return alert(getBankUiText('validExpiryDate'));
          if (!cardCvv || cardCvv.length < 3) return alert(getBankUiText('validCvv'));
          if (!cardName) return alert(getBankUiText('cardholderName'));
        }
        const loadingEl = document.getElementById('add-money-loading');
        const submitBtn = document.getElementById('add-money-submit');
        if (loadingEl) loadingEl.style.display = 'block';
        if (submitBtn) submitBtn.disabled = true;
        fetch('api/transactions.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({action: 'add', amount: amount, payment_method: paymentMethod, csrf_token: csrf})
        })
        .then(r => r.json())
        .then(j => {
          if (loadingEl) loadingEl.style.display = 'none';
          if (submitBtn) submitBtn.disabled = false;
          if (j.error) return alert(localizeBankMessage(j.error_code || j.error));
          if (j.balance !== undefined) state.balance = j.balance;
          updateBalanceDisplay();
          if (j.tx) state.txs.push(j.tx);
          alert(getBankUiText('moneyAddedSuccess'));
          if (addMoneyForm) addMoneyForm.reset();
          if (addMoneyModal) addMoneyModal.style.display = 'none';
        })
        .catch(() => {
          if (loadingEl) loadingEl.style.display = 'none';
          if (submitBtn) submitBtn.disabled = false;
          alert(getBankUiText('networkError'));
        });
      });

      // Send Money
      if (sendForm) sendForm.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!csrf) {
          alert(getBankUiText('securityTokenNotLoaded'));
          return;
        }
        const to = sendForm.querySelector('input[name="to"]').value.trim();
        const amount = parseFloat(sendForm.querySelector('input[name="amount"]').value);
        if (!to || !amount || amount <= 0) return alert(getBankUiText('enterRecipientAndAmount'));
        // Always fetch latest balance before sending
        fetch('api/transactions.php', {credentials:'same-origin'})
          .then(r => r.json())
          .then(data => {
            state.balance = data.balance || 0;
            updateBalanceDisplay();
            if (amount > state.balance) {
              alert(getBankUiText('insufficientBalance'));
              return;
            }
            // Show loading indicator
            const loadingEl = document.getElementById('send-loading');
            const submitBtn = document.getElementById('send-submit');
            if (loadingEl) loadingEl.style.display = 'block';
            if (submitBtn) submitBtn.disabled = true;
            // POST to server
            fetch('api/transactions.php', {
              method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'},
              body: JSON.stringify({action:'send', to: to, amount: amount, csrf_token: csrf})
            }).then(r=>{
              if (!r.ok) {
                return r.text().then(text => {
                  showErrorModal(getBankUiText('serverErrorWithMessage', { message: text }));
                  throw new Error(`Server returned ${r.status}`);
                });
              }
              return r.json();
            }).then(j=>{
              if (loadingEl) loadingEl.style.display = 'none';
              if (submitBtn) submitBtn.disabled = false;
              if (j.error) {
                showErrorModal(localizeBankMessage(j.error_code || j.error));
                return;
              }
              if (j.success === true) {
                if (j.balance !== undefined) state.balance = j.balance;
                updateBalanceDisplay();
                if (j.tx) state.txs.push(j.tx);
                if (sendForm) sendForm.reset();
                if (sendModal) sendModal.style.display = 'none';
                // Show receipt modal
                showReceiptModal(to, amount, j.tx ? j.tx.id : undefined);
                render && render();
              } else {
                showErrorModal(getBankUiText('transferUnexpected'));
              }
            }).catch(err => {
              showErrorModal(getBankUiText('networkErrorWithMessage', { message: err.message }));
            });
          });
      // Show a receipt modal after sending money
      function showReceiptModal(recipient, amount, txId) {
        const now = new Date();
        const transactionId = txId || 'TXN' + now.getFullYear() + (now.getMonth()+1).toString().padStart(2,'0') + now.getDate().toString().padStart(2,'0') + now.getHours().toString().padStart(2,'0') + now.getMinutes().toString().padStart(2,'0') + now.getSeconds().toString().padStart(2,'0') + Math.floor(Math.random()*1000).toString().padStart(3,'0');
        const locale = getActiveLanguage();
        const currentTime = now.toLocaleTimeString(locale, {hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true});
        const currentDate = now.toLocaleDateString(locale, {month: 'short', day: 'numeric', year: 'numeric'});
        // Try to get sender first name from state or fallback to email
        let senderName = '';
        if (state && state.first_name && state.first_name.trim()) {
          senderName = state.first_name.trim();
        } else if (state && state.name && state.name.trim()) {
          senderName = state.name.trim().split(' ')[0];
        } else if (state && state.email && state.email.trim()) {
          senderName = state.email.split('@')[0];
        } else {
          senderName = 'Unknown';
        }
        const modal = document.createElement('div');
        modal.id = 'send-receipt-modal';
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(15,23,42,0.92);backdrop-filter:blur(8px);z-index:10000;display:flex;align-items:center;justify-content:center;animation:fadeIn 0.3s ease;';
        modal.innerHTML = `
          <div id="send-receipt-card" style="background:linear-gradient(135deg,#f0f9ff 0%,#fff 100%);border-radius:32px;padding:0;max-width:440px;width:95vw;box-shadow:0 40px 100px rgba(2,132,199,0.18),0 0 0 1px rgba(2,132,199,0.08);overflow:hidden;position:relative;">
            <div style="background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);padding:36px 24px 24px 24px;text-align:center;position:relative;overflow:hidden;">
              <div style="position:absolute;top:-40px;left:-40px;width:120px;height:120px;background:radial-gradient(circle,rgba(255,255,255,0.18) 0%,transparent 80%);"></div>
              <div style="font-size:60px;margin-bottom:12px;">≡ƒÄë</div>
              <h2 style="font-size:clamp(24px,5vw,32px);font-weight:900;color:#fff;margin:0 0 8px 0;letter-spacing:-0.5px;text-shadow:0 4px 12px rgba(2,132,199,0.18);">${translateUi('transfer-successful-title', 'Transfer Successful!')}</h2>
              <div style="color:#bae6fd;font-size:clamp(13px,3vw,15px);font-weight:500;">${translateUi('payment-processed', 'Your payment has been processed')}</div>
            </div>
            <div style="padding:clamp(24px,5vw,36px);position:relative;">
              <div style="background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);border-radius:20px;border:2px solid #86efac;box-shadow:0 8px 32px rgba(134,239,172,0.12);padding:20px 0 12px 0;margin-bottom:24px;">
                <div style="color:#166534;font-size:clamp(12px,2.8vw,14px);font-weight:700;margin-bottom:10px;text-transform:uppercase;letter-spacing:1px">≡ƒÆ░ ${translateUi('amount-sent-label', 'Amount Sent')}</div>
                <div style="color:#166534;font-weight:900;font-size:clamp(38px,9vw,48px);letter-spacing:-2px;text-shadow:0 2px 4px rgba(22,101,52,0.1);margin-bottom:8px">$${parseFloat(amount).toFixed(2)}</div>
              </div>
              <div style="margin-bottom:8px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">≡ƒæñ <span>${translateUi('sender-label', 'Sender')}:</span> <span style="font-weight:700">${senderName}</span></div>
              <div style="margin-bottom:18px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">≡ƒôñ <span>${translateUi('to-label', 'To')}:</span> <span style="font-weight:700">${recipient}</span></div>
              <div style="margin-bottom:8px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">≡ƒôà <span>${translateUi('date-label', 'Date')}:</span> <span style="font-weight:700">${currentDate}</span></div>
              <div style="margin-bottom:8px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">ΓÅ░ <span>${translateUi('time-label', 'Time')}:</span> <span style="font-weight:700">${currentTime}</span></div>
              <div style="margin-bottom:18px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">≡ƒåö <span>${translateUi('transaction-id-label', 'Transaction ID')}:</span> <span style="font-weight:700">${transactionId}</span></div>
              <div style="margin-bottom:18px;font-size:15px;color:#22c55e;font-weight:700;text-align:center;">Γ£à ${translateUi('status-label', 'Status')}: ${translateUi('status-completed', 'Completed')}</div>
              <div style="margin-bottom:18px;font-size:13px;color:#64748b;text-align:center;">${translateUi('sent-via-wallet', 'Sent via Mivonta')}</div>
              <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;">
                <button id="share-send-receipt-btn" style="flex:1;padding:14px;background:linear-gradient(135deg,#f59e42 0%,#fbbf24 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(251,191,36,0.3);display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.3s ease;min-height:48px">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 17l4-4 4 4"/><path d="M8 13h8"/></svg>
                  ${translateUi('share-as-image', 'Share as Image')}
                </button>
                <button id="close-send-receipt-btn" style="flex:1;padding:14px;background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(2,132,199,0.3);display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.3s ease;min-height:48px">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                  ${translateUi('done-btn', 'Done')}
                </button>
              </div>
            </div>
          </div>
        `;
        document.body.appendChild(modal);
        // Share as Image functionality
        document.getElementById('share-send-receipt-btn').onclick = function() {
          if (typeof html2canvas === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
            script.onload = () => captureSendReceiptImage();
            document.body.appendChild(script);
          } else {
            captureSendReceiptImage();
          }
          function captureSendReceiptImage() {
            const card = document.getElementById('send-receipt-card');
            html2canvas(card, {backgroundColor: null, scale: 2}).then(canvas => {
              if (navigator.share && canvas.toBlob) {
                canvas.toBlob(blob => {
                  const file = new File([blob], 'receipt.png', {type: 'image/png'});
                  navigator.share({files: [file], title: translateUi('payment-receipt', 'Payment Receipt'), text: translateUi('transfer-successful-title', 'Transfer Successful!')}).catch(() => {
                    const url = URL.createObjectURL(blob);
                    window.open(url, '_blank');
                  });
                });
              } else {
                const url = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.href = url;
                link.download = 'receipt.png';
                link.click();
              }
            });
          }
        };
        // Close/Done button: update balance and go to dashboard
        document.getElementById('close-send-receipt-btn').onclick = () => {
          modal.remove();
          fetchAndUpdateBalance();
          window.location.href = 'dashboard.php';
        };
      }
      });

      // Withdraw logic (if present in your UI)
      // ...existing code for withdraw, ensure after success:
      // if (data.balance !== undefined) state.balance = data.balance; updateBalanceDisplay();

    });
});
      // ADD MONEY FORM SUBMIT
      if (addMoneyForm) addMoneyForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const paymentMethod = paymentMethodSelect.value;
        const amount = parseFloat(document.getElementById('add-amount').value);
        if (!paymentMethod) return alert(getBankUiText('pleaseSelectPaymentMethod'));
        if (!amount || amount <= 0) return alert(getBankUiText('enterPositiveAmount'));
        if (amount > 10000) return alert(getBankUiText('maxAmount'));
        if (paymentMethod === 'card') {
          const cardNumber = document.getElementById('card-number').value.replace(/\s/g, '');
          const cardExpiry = document.getElementById('card-expiry').value;
          const cardCvv = document.getElementById('card-cvv').value;
          const cardName = document.getElementById('card-name').value.trim();
          if (!cardNumber || cardNumber.length < 13) return alert(getBankUiText('validCardNumber'));
          if (!cardExpiry || cardExpiry.length !== 5) return alert(getBankUiText('validExpiryDate'));
          if (!cardCvv || cardCvv.length < 3) return alert(getBankUiText('validCvv'));
          if (!cardName) return alert(getBankUiText('cardholderName'));
        }
        const loadingEl = document.getElementById('add-money-loading');
        const submitBtn = document.getElementById('add-money-submit');
        if (loadingEl) loadingEl.style.display = 'block';
        if (submitBtn) submitBtn.disabled = true;
        fetch('api/transactions.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({action: 'add', amount: amount, payment_method: paymentMethod, csrf_token: csrf})
        })
        .then(r => r.json())
        .then(j => {
          if (loadingEl) loadingEl.style.display = 'none';
          if (submitBtn) submitBtn.disabled = false;
          if (j.error) return alert(localizeBankMessage(j.error_code || j.error));
          if (j.balance !== undefined) state.balance = j.balance;
          updateBalanceDisplay();
          if (j.tx) state.txs.push(j.tx);
          alert(getBankUiText('moneyAddedSuccess'));
          render();
          addMoneyForm.reset();
          addMoneyModal.style.display = 'none';
        })
        .catch(() => {
          if (loadingEl) loadingEl.style.display = 'none';
          if (submitBtn) submitBtn.disabled = false;
          alert(getBankUiText('networkError'));
        });
      });


  // Sync settings changes back to main selectors

  // Guard for settingsLangSelect and langSelect
  if (typeof settingsLangSelect !== 'undefined' && typeof langSelect !== 'undefined' && settingsLangSelect && langSelect) {
    settingsLangSelect.addEventListener('change', (e) => {
      langSelect.value = e.target.value;
      langSelect.dispatchEvent(new Event('change'));
      if (window.i18n && typeof window.i18n.setLanguage === 'function') {
        window.i18n.setLanguage(e.target.value);
      }
      // Update Add Bank button and bank selection UI
      updateBankUITranslations();
      localizeRecentActivity();
      localizeBankDynamicUi();
    });
  }

  // Update Add Bank and bank selection UI translations
  function updateBankUITranslations() {
    // Update Add Bank button
    var addBankBtn = document.getElementById('add-bank-btn');
    if (addBankBtn && window.i18n) {
      addBankBtn.textContent = window.i18n.t('add-account-btn');
    }
    // Update bank selection cards
    document.querySelectorAll('.bank-account-card').forEach(function(card) {
      var bankNameDiv = card.querySelector('.bank-name');
      if (bankNameDiv && window.i18n) {
        // Only update the label, keep the emoji and bank name
        var parts = bankNameDiv.textContent.split(' ');
        if (parts.length > 1) {
          bankNameDiv.innerHTML = '≡ƒÅª ' + parts.slice(1).join(' ');
        }
      }
      // Optionally update other fields if needed
    });
    // Update no banks message
    var noBanksMsg = document.getElementById('no-banks-message');
    if (noBanksMsg && window.i18n) {
      noBanksMsg.textContent = window.i18n.t('no-accounts');
    }
    // Update bank selection label if present
    var selectBankLabel = document.getElementById('select-bank-label');
    if (selectBankLabel && window.i18n) {
      selectBankLabel.textContent = window.i18n.t('select-bank');
    }
  }

  // Guard for updateAmountPlaceholder if called from HTML
  if (typeof updateAmountPlaceholder === 'undefined') {
    window.updateAmountPlaceholder = function(){};
  }




  // Listen for country change events from other tabs
  window.addEventListener('countryChanged', () => {
    render();
  });

  window.addEventListener('storage', (e) => {
    if (e.key === 'mw_lang') {
      localizeRecentActivity();
      localizeBankDynamicUi();
    }
  });

  window.addEventListener('i18n:rendered', () => {
    localizeRecentActivity();
    localizeBankDynamicUi();
  });

  // Listen for exchange rate updates
  window.addEventListener('ratesUpdated', () => {
    render(); // Re-render with new exchange rates
  });

  render();
  // Beautiful success modal with share functionality
  function showSuccessModal(recipient, amount) {
  console.log('showSuccessModal called with:', recipient, amount);
  
  // Generate transaction details
  const now = new Date();
  const transactionId = 'TXN' + now.getFullYear() + (now.getMonth()+1).toString().padStart(2,'0') + now.getDate().toString().padStart(2,'0') + now.getHours().toString().padStart(2,'0') + now.getMinutes().toString().padStart(2,'0') + now.getSeconds().toString().padStart(2,'0') + Math.floor(Math.random()*1000).toString().padStart(3,'0');
  const sessionId = 'SES' + Date.now() + Math.random().toString(36).substr(2, 9).toUpperCase();
  const referenceNumber = 'REF' + Math.random().toString(36).substr(2, 12).toUpperCase();
  
  // Get sender name and email from state
  const senderName = state.sender_name || state.name || '';
  const senderEmail = state.email || '';
  console.log('Sender name:', senderName, 'Sender email:', senderEmail);
  // Mask email function (same as incoming alert)
  function maskEmail(email) {
    const [user, domain] = email.split('@');
    if (user.length <= 2) return email;
    const first = user[0];
    const last = user[user.length-1];
    return first + '*'.repeat(user.length-2) + last + '@' + domain;
  }
  const maskedSenderEmail = maskEmail(senderEmail);
  const locale = getActiveLanguage();
  const currentTime = now.toLocaleTimeString(locale, {hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true});
  const currentDate = now.toLocaleDateString(locale, {month: 'short', day: 'numeric', year: 'numeric'});
  
  console.log('Creating modal element');
  const modal = document.createElement('div');
  modal.id = 'receipt-modal';
  modal.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.9);backdrop-filter:blur(12px);display:flex;align-items:center;justify-content:center;z-index:10000;animation:fadeIn 0.4s ease;overflow-y:auto;padding:20px 10px';
  
  // Create inner card
  const card = document.createElement('div');
  card.id = 'receipt-card';
  card.style.cssText = 'background:#ffffff;border-radius:28px;padding:0;max-width:580px;width:95%;box-shadow:0 40px 100px rgba(0,0,0,0.35),0 0 0 1px rgba(255,255,255,0.1);overflow:hidden;animation:slideUpBounce 0.6s cubic-bezier(0.34,1.56,0.64,1);margin:auto;position:relative';
  
  card.innerHTML = 
    // Decorative background pattern
    '<div style="position:absolute;top:0;left:0;right:0;height:200px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);opacity:0.05;pointer-events:none"></div>' +
    
    // Header with animation
    '<div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px 24px;text-align:center;position:relative;overflow:hidden">' +
    '<div style="position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 70%);animation:pulse 3s ease-in-out infinite"></div>' +
    '<div style="width:100px;height:100px;background:rgba(255,255,255,0.2);backdrop-filter:blur(10px);border-radius:50%;margin:0 auto 24px;display:flex;align-items:center;justify-content:center;box-shadow:0 12px 40px rgba(0,0,0,0.25);border:3px solid rgba(255,255,255,0.3);animation:scaleInBounce 0.8s cubic-bezier(0.34,1.56,0.64,1) 0.2s both">' +
    '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>' +
    '</div>' +
    '<h2 style="font-size:clamp(28px,6vw,36px);font-weight:900;color:#fff;margin:0 0 8px 0;letter-spacing:-0.5px;text-shadow:0 4px 12px rgba(0,0,0,0.2);animation:slideInFromTop 0.6s ease 0.3s both">' + translateUi('transfer-successful-title', 'Transfer Successful!') + '</h2>' +
    '<p style="font-size:clamp(14px,3.5vw,16px);color:rgba(255,255,255,0.95);margin:0;font-weight:500;animation:slideInFromTop 0.6s ease 0.4s both">≡ƒÄë ' + translateUi('payment-processed', 'Your payment has been processed') + '</p>' +
    '</div>' +
    
    // Main content
    '<div style="padding:clamp(24px,5vw,36px);position:relative">' +
    
    // Amount display - Enhanced with animation
    '<div style="text-align:center;margin-bottom:28px;padding:24px;background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);border-radius:20px;border:2px solid #86efac;box-shadow:0 8px 32px rgba(134,239,172,0.2);position:relative;overflow:hidden">' +
    '<div style="position:absolute;top:0;left:0;right:0;height:100%;background:linear-gradient(45deg,transparent 48%,rgba(255,255,255,0.5) 50%,transparent 52%);background-size:200% 200%;animation:shimmer 3s linear infinite"></div>' +
    '<div style="color:#166534;font-size:clamp(12px,2.8vw,14px);font-weight:700;margin-bottom:12px;text-transform:uppercase;letter-spacing:1px">≡ƒÆ╕ ' + translateUi('amount-sent-label', 'Amount Sent') + '</div>' +
    '<div style="color:#166534;font-weight:900;font-size:clamp(38px,9vw,48px);letter-spacing:-2px;text-shadow:0 2px 4px rgba(22,101,52,0.1);margin-bottom:8px">$' + parseFloat(amount).toFixed(2) + '</div>' +
    '<div style="display:inline-flex;align-items:center;gap:6px;background:rgba(22,101,52,0.15);padding:6px 16px;border-radius:20px;font-size:clamp(11px,2.5vw,13px);color:#166534;font-weight:700">' +
    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>' +
    translateUi('completed-successfully', 'Completed Successfully') +
    '</div>' +
    '</div>' +
    
    // Transaction details with modern card design
    '<div style="background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);border-radius:18px;padding:20px;margin-bottom:20px;border:1px solid #e2e8f0;box-shadow:0 4px 16px rgba(0,0,0,0.06)">' +
    
    // Detail row with icons
    '<div style="display:flex;align-items:center;padding:12px 0;border-bottom:1px solid #e2e8f0">' +
    '<div style="flex-shrink:0;width:36px;height:36px;background:linear-gradient(135deg,#dbeafe,#bfdbfe);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-right:12px">' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>' +
    '</div>' +
    '<div style="flex:1;min-width:0">' +
    '<div style="color:#64748b;font-size:11px;font-weight:600;margin-bottom:3px">' + translateUi('from-label', 'From').toUpperCase() + '</div>' +
    '<div style="color:#0f172a;font-size:clamp(13px,3vw,14px);font-weight:700;word-break:break-all">' + escapeHtml(senderName) + '</div>' +
    '<div style="color:#64748b;font-size:11px;font-weight:600;margin-bottom:3px">' + translateUi('sender-email-label', 'Sender Email') + '</div>' +
    '<div style="color:#0f172a;font-size:clamp(13px,3vw,14px);font-weight:700;word-break:break-all">' + escapeHtml(maskedSenderEmail) + '</div>' +
    '</div>' +
    '</div>' +
    
    '<div style="display:flex;align-items:center;padding:12px 0;border-bottom:1px solid #e2e8f0">' +
    '<div style="flex-shrink:0;width:36px;height:36px;background:linear-gradient(135deg,#fef3c7,#fde68a);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-right:12px">' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>' +
    '</div>' +
    '<div style="flex:1;min-width:0">' +
    '<div style="color:#64748b;font-size:11px;font-weight:600;margin-bottom:3px">' + translateUi('to-label', 'To').toUpperCase() + '</div>' +
    '<div style="color:#0f172a;font-size:clamp(13px,3vw,14px);font-weight:700;word-break:break-all">' + escapeHtml(recipient) + '</div>' +
    '</div>' +
    '</div>' +
    
    '<div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #e2e8f0">' +
    '<div style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:clamp(12px,2.8vw,13px);font-weight:600">' +
    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>' +
    translateUi('time-label', 'Time') +
    '</div>' +
    '<div style="color:#0f172a;font-size:clamp(12px,2.8vw,13px);font-weight:700">' + currentTime + '</div>' +
    '</div>' +
    
    '<div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #e2e8f0">' +
    '<div style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:clamp(12px,2.8vw,13px);font-weight:600">' +
    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>' +
    translateUi('date-label', 'Date') +
    '</div>' +
    '<div style="color:#0f172a;font-size:clamp(12px,2.8vw,13px);font-weight:700">' + currentDate + '</div>' +
    '</div>' +
    
    '<div style="padding:12px 0">' +
    '<div style="color:#64748b;font-size:11px;font-weight:600;margin-bottom:6px">' + translateUi('transaction-id-label', 'Transaction ID').toUpperCase() + '</div>' +
    '<div style="background:#fff;padding:10px 12px;border-radius:8px;color:#0f172a;font-family:monospace;font-size:clamp(10px,2.5vw,11px);font-weight:700;word-break:break-all;border:1px solid #e2e8f0">' + transactionId + '</div>' +
    '</div>' +
    
    '<div style="padding:12px 0 0 0">' +
    '<div style="color:#64748b;font-size:11px;font-weight:600;margin-bottom:6px">SESSION ID</div>' +
    '<div style="background:#fff;padding:10px 12px;border-radius:8px;color:#0f172a;font-family:monospace;font-size:clamp(10px,2.5vw,11px);font-weight:700;word-break:break-all;border:1px solid #e2e8f0">' + sessionId + '</div>' +
    '</div>' +
    
    '</div>' +
    
    // Action buttons - Share, Share as Image, and Done
    '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px">' +
    '<button id="share-receipt-btn" style="padding:clamp(14px,3.5vw,16px);background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(16,185,129,0.3);display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.3s ease;min-height:48px">' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>' +
    translateUi('share-btn', 'Share') +
    '</button>' +
    '<button id="share-receipt-img-btn" style="padding:clamp(14px,3.5vw,16px);background:linear-gradient(135deg,#f59e42 0%,#fbbf24 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(251,191,36,0.3);display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.3s ease;min-height:48px">' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 17l4-4 4 4"/><path d="M8 13h8"/></svg>' +
    translateUi('share-as-image', 'Share as Image') +
    '</button>' +
    '<button onclick="this.closest(\'#receipt-modal\').remove();location.reload()" style="padding:clamp(14px,3.5vw,16px);background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border:none;border-radius:14px;font-size:clamp(14px,3.5vw,15px);font-weight:800;cursor:pointer;box-shadow:0 6px 20px rgba(102,126,234,0.3);display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.3s ease;min-height:48px">' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>' +
    translateUi('done-btn', 'Done') +
    '</button>' +
    '</div>' +
    
    // Footer text
    '<div style="text-align:center;padding-top:16px;border-top:1px solid #e2e8f0">' +
    '<p style="color:#94a3b8;font-size:clamp(11px,2.5vw,12px);margin:0;font-weight:500">≡ƒöÆ ' + translateUi('transaction-secure', 'This transaction is secure and encrypted') + '</p>' +
    '</div>' +
    
    '</div>';
  
  modal.appendChild(card);
	  document.body.appendChild(modal);


}
  
  // Add share functionality
  const shareBtn = document.getElementById('share-receipt-btn');
  if (shareBtn) {
    shareBtn.addEventListener('click', function() {
      const receiptText = `≡ƒÄë ${translateUi('transfer-successful-title', 'Transfer Successful!')}\n\n≡ƒÆ░ ${translateUi('amount-label', 'Amount')}: $${parseFloat(amount).toFixed(2)}\n≡ƒôñ ${translateUi('to-label', 'To')}: ${recipient}\n≡ƒôà ${translateUi('date-label', 'Date')}: ${currentDate}\nΓÅ░ ${translateUi('time-label', 'Time')}: ${currentTime}\n≡ƒåö ${translateUi('transaction-id-label', 'Transaction ID')}: ${transactionId}\n\nΓ£à ${translateUi('status-label', 'Status')}: ${translateUi('status-completed', 'Completed')}\n\n${translateUi('sent-via-wallet', 'Sent via Mivonta')}`;
      if (navigator.share) {
        navigator.share({
          title: translateUi('payment-receipt', 'Payment Receipt'),
          text: receiptText
        }).catch(() => {
          copyToClipboard(receiptText);
        });
      } else {
        copyToClipboard(receiptText);
      }
    });
  }

  // Share as Image functionality (shorter image)
  const shareImgBtn = document.getElementById('share-receipt-img-btn');
  if (shareImgBtn) {
    shareImgBtn.addEventListener('click', function() {
      // Load html2canvas if not loaded
      if (typeof html2canvas === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
        script.onload = () => captureShortReceiptImage();
        document.body.appendChild(script);
      } else {
        captureShortReceiptImage();
      }
    });
  }
  // Inject animation CSS for modals and buttons
  const style = document.createElement('style');
  style.textContent = `
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUpBounce { 
      0% { transform: translateY(100px); opacity: 0; }
      60% { transform: translateY(-10px); }
      100% { transform: translateY(0); opacity: 1; }
    }
    @keyframes scaleInBounce {
      0% { transform: scale(0); opacity: 0; }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); opacity: 1; }
    }
    @keyframes slideInFromTop {
      from { transform: translateY(-20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    @keyframes pulse {
      0%, 100% { opacity: 0.3; transform: scale(1); }
      50% { opacity: 0.5; transform: scale(1.05); }
    }
    @keyframes shimmer {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }
    #share-receipt-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(16,185,129,0.4); }
    #receipt-modal button:active { transform: scale(0.98); }
    @media (max-width: 480px) {
      #receipt-card { border-radius: 24px; }
    }
  `;
  document.head.appendChild(style);


// End of showSuccessModal

// End of bank.js
