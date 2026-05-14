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
  ko: { received: '다음으로부터 수신', sent: '다음으로 전송', added: '돈이 추가됨', addedByAdmin: '관리자가 추가한 금액', transferFrom: '다음으로부터 이체', withdrawal: '다음 계좌로 출금', openingBalance: '초기 잔액', approved: '승인됨', pending: '대기 중', processing: '처리 중', failed: '실패' },
  ja: { received: '受取元', sent: '送金先', added: '資金が追加されました', addedByAdmin: '管理者が追加した資金', transferFrom: '送金元', withdrawal: '出金先', openingBalance: '開始残高', approved: '承認済み', pending: '保留中', processing: '処理中', failed: '失敗' },
  'zh-tw': { received: '來自', sent: '發送至', added: '已新增資金', addedByAdmin: '管理員新增的資金', transferFrom: '轉帳自', withdrawal: '提款至', openingBalance: '初始餘額', approved: '已核准', pending: '待處理', processing: '處理中', failed: '失敗' },
  ar: { received: 'تم الاستلام من', sent: 'تم الإرسال إلى', added: 'تمت إضافة الأموال', addedByAdmin: 'تمت إضافة الأموال بواسطة المسؤول', transferFrom: 'تحويل من', withdrawal: 'سحب إلى', openingBalance: 'الرصيد الافتتاحي', approved: 'تمت الموافقة', pending: 'قيد الانتظار', processing: 'جار المعالجة', failed: 'فشل' },
  hi: { received: 'से प्राप्त', sent: 'को भेजा गया', added: 'पैसे जोड़े गए', addedByAdmin: 'एडमिन द्वारा जोड़े गए पैसे', transferFrom: 'से ट्रांसफर', withdrawal: 'निकासी हेतु', openingBalance: 'प्रारंभिक शेष', approved: 'स्वीकृत', pending: 'लंबित', processing: 'प्रसंस्करण में', failed: 'विफल' },
  ru: { received: 'Получено от', sent: 'Отправлено', added: 'Деньги добавлены', addedByAdmin: 'Деньги добавлены администратором', transferFrom: 'Перевод от', withdrawal: 'Вывод на', openingBalance: 'Начальный баланс', approved: 'Одобрено', pending: 'В ожидании', processing: 'Обрабатывается', failed: 'Ошибка' },
  nl: { received: 'Ontvangen van', sent: 'Verzonden naar', added: 'Geld toegevoegd', addedByAdmin: 'Geld toegevoegd door beheerder', transferFrom: 'Overboeking van', withdrawal: 'Opname naar', openingBalance: 'Beginsaldo', approved: 'Goedgekeurd', pending: 'In behandeling', processing: 'Wordt verwerkt', failed: 'Mislukt' }
};

const supportLabelText = {
  en: 'Support',
  es: 'Soporte',
  fr: 'Support',
  de: 'Support',
  it: 'Supporto',
  pt: 'Suporte',
  ko: '지원',
  ja: 'サポート',
  'zh-tw': '支援',
  ar: 'الدعم',
  hi: 'सहायता',
  ru: 'Поддержка',
  nl: 'Ondersteuning'
};

const bankUiText = {
  en: {
    addMoneyTitle: 'Add Money',
    addMoneySubtitle: 'Deposit funds to your account',
    enterAmount: 'Enter amount',
    selectPaymentMethod: 'Select payment method',
    cardOption: 'Card',
    bankTransferOption: 'Bank Transfer',
    addMoneyButton: 'Add Money',
    cancelButton: 'Cancel',
    processing: 'Processing...',
    pleaseSelectPaymentMethod: 'Please select a payment method',
    enterPositiveAmount: 'Enter a valid positive amount',
    maxAmount: 'Maximum amount per transaction is $10,000',
    validCardNumber: 'Please enter a valid card number',
    validExpiryDate: 'Please enter valid expiry date (MM/YY)',
    validCvv: 'Please enter valid CVV',
    cardholderName: 'Please enter cardholder name',
    moneyAddedSuccess: 'Money added successfully!',
    networkError: 'Network error',
    securityTokenNotLoaded: 'Security token not loaded yet. Please wait a moment and try again.',
    enterRecipientAndAmount: 'Enter recipient and a valid positive amount',
    insufficientBalance: 'Insufficient balance',
    transferUnexpected: 'Transfer completed but response format unexpected. Please refresh the page.',
    networkErrorWithMessage: 'Network error: {message}',
    serverErrorWithMessage: 'Server error: {message}',
    withdrawalExceedsBalance: 'Withdrawal amount exceeds your available balance.',
    validSixDigitCode: 'Please enter a valid 6-digit code.',
    withdrawalFailed: 'Withdrawal failed.',
    invalidOrExpiredCode: 'Invalid or expired code.',
    networkErrorTryAgain: 'Network error. Please try again.',
    withdrawLoadingDescription: 'Verifying your code and preparing the transfer request.',
    withdrawLoadingCodeCheck: 'Code check',
    withdrawLoadingSecureQueue: 'Secure queue',
    withdrawLoadingStatusUpdate: 'Status update'
  },
  es: {
    addMoneyTitle: 'Agregar dinero',
    addMoneySubtitle: 'Deposita fondos en tu cuenta',
    enterAmount: 'Ingresa el monto',
    selectPaymentMethod: 'Selecciona el metodo de pago',
    cardOption: 'Tarjeta',
    bankTransferOption: 'Transferencia bancaria',
    addMoneyButton: 'Agregar dinero',
    cancelButton: 'Cancelar',
    processing: 'Procesando...',
    pleaseSelectPaymentMethod: 'Por favor selecciona un metodo de pago',
    enterPositiveAmount: 'Ingresa un monto positivo valido',
    maxAmount: 'El monto maximo por transaccion es $10,000',
    validCardNumber: 'Por favor ingresa un numero de tarjeta valido',
    validExpiryDate: 'Por favor ingresa una fecha de vencimiento valida (MM/AA)',
    validCvv: 'Por favor ingresa un CVV valido',
    cardholderName: 'Por favor ingresa el nombre del titular',
    moneyAddedSuccess: 'Dinero agregado correctamente',
    networkError: 'Error de red',
    securityTokenNotLoaded: 'El token de seguridad aun no se ha cargado. Espera un momento e intentalo de nuevo.',
    enterRecipientAndAmount: 'Ingresa el destinatario y un monto positivo valido',
    insufficientBalance: 'Saldo insuficiente',
    transferUnexpected: 'La transferencia se completo, pero el formato de la respuesta fue inesperado. Actualiza la pagina.',
    networkErrorWithMessage: 'Error de red: {message}',
    serverErrorWithMessage: 'Error del servidor: {message}',
    withdrawalExceedsBalance: 'El monto del retiro excede tu saldo disponible.',
    validSixDigitCode: 'Por favor ingresa un codigo valido de 6 digitos.',
    withdrawalFailed: 'El retiro fallo.',
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
    addMoneyTitle: '자금 추가',
    addMoneySubtitle: '계정에 자금을 입금하세요',
    enterAmount: '금액 입력',
    selectPaymentMethod: '결제 방법 선택',
    cardOption: '카드',
    bankTransferOption: '은행 송금',
    addMoneyButton: '자금 추가',
    cancelButton: '취소',
    processing: '처리 중...',
    pleaseSelectPaymentMethod: '결제 방법을 선택하세요',
    enterPositiveAmount: '유효한 양수 금액을 입력하세요',
    maxAmount: '거래당 최대 금액은 $10,000입니다',
    validCardNumber: '유효한 카드 번호를 입력하세요',
    validExpiryDate: '유효한 만료일을 입력하세요 (MM/YY)',
    validCvv: '유효한 CVV를 입력하세요',
    cardholderName: '카드 소유자 이름을 입력하세요',
    moneyAddedSuccess: '자금이 성공적으로 추가되었습니다',
    networkError: '네트워크 오류',
    securityTokenNotLoaded: '보안 토큰이 아직 로드되지 않았습니다. 잠시 후 다시 시도하세요.',
    enterRecipientAndAmount: '수신자와 유효한 양수 금액을 입력하세요',
    insufficientBalance: '잔액이 부족합니다',
    transferUnexpected: '이체가 완료되었지만 응답 형식이 예상과 다릅니다. 페이지를 새로고침하세요.',
    networkErrorWithMessage: '네트워크 오류: {message}',
    serverErrorWithMessage: '서버 오류: {message}',
    withdrawalExceedsBalance: '출금 금액이 사용 가능한 잔액을 초과합니다.',
    validSixDigitCode: '유효한 6자리 코드를 입력하세요.',
    withdrawalFailed: '출금에 실패했습니다.',
    invalidOrExpiredCode: '코드가 유효하지 않거나 만료되었습니다.',
    networkErrorTryAgain: '네트워크 오류입니다. 다시 시도하세요.',
    withdrawLoadingDescription: '코드를 확인하고 이체 요청을 준비하고 있습니다.',
    withdrawLoadingCodeCheck: '코드 확인',
    withdrawLoadingSecureQueue: '보안 대기열',
    withdrawLoadingStatusUpdate: '상태 업데이트'
  },
  ja: {
    addMoneyTitle: '資金を追加',
    addMoneySubtitle: '口座に資金を入金します',
    enterAmount: '金額を入力',
    selectPaymentMethod: '支払い方法を選択',
    cardOption: 'カード',
    bankTransferOption: '銀行振込',
    addMoneyButton: '資金を追加',
    cancelButton: 'キャンセル',
    processing: '処理中...',
    pleaseSelectPaymentMethod: '支払い方法を選択してください',
    enterPositiveAmount: '有効な正の金額を入力してください',
    maxAmount: '1回の取引あたりの上限額は$10,000です',
    validCardNumber: '有効なカード番号を入力してください',
    validExpiryDate: '有効な有効期限を入力してください (MM/YY)',
    validCvv: '有効なCVVを入力してください',
    cardholderName: 'カード名義人を入力してください',
    moneyAddedSuccess: '資金が正常に追加されました',
    networkError: 'ネットワークエラー',
    securityTokenNotLoaded: 'セキュリティトークンがまだ読み込まれていません。少し待ってから再試行してください。',
    enterRecipientAndAmount: '受取人と有効な正の金額を入力してください',
    insufficientBalance: '残高不足です',
    transferUnexpected: '送金は完了しましたが、応答形式が予期したものではありません。ページを更新してください。',
    networkErrorWithMessage: 'ネットワークエラー: {message}',
    serverErrorWithMessage: 'サーバーエラー: {message}',
    withdrawalExceedsBalance: '出金額が利用可能残高を超えています。',
    validSixDigitCode: '有効な6桁のコードを入力してください。',
    withdrawalFailed: '出金に失敗しました。',
    invalidOrExpiredCode: 'コードが無効か期限切れです。',
    networkErrorTryAgain: 'ネットワークエラーです。もう一度お試しください。',
    withdrawLoadingDescription: 'コードを確認し、送金リクエストを準備しています。',
    withdrawLoadingCodeCheck: 'コード確認',
    withdrawLoadingSecureQueue: '安全なキュー',
    withdrawLoadingStatusUpdate: 'ステータス更新'
  },
  'zh-tw': {
    addMoneyTitle: '新增資金',
    addMoneySubtitle: '將資金存入您的帳戶',
    enterAmount: '輸入金額',
    selectPaymentMethod: '選擇付款方式',
    cardOption: '卡片',
    bankTransferOption: '銀行轉帳',
    addMoneyButton: '新增資金',
    cancelButton: '取消',
    processing: '處理中...',
    pleaseSelectPaymentMethod: '請選擇付款方式',
    enterPositiveAmount: '請輸入有效的正數金額',
    maxAmount: '每筆交易的最高金額為 $10,000',
    validCardNumber: '請輸入有效的卡號',
    validExpiryDate: '請輸入有效的到期日 (MM/YY)',
    validCvv: '請輸入有效的 CVV',
    cardholderName: '請輸入持卡人姓名',
    moneyAddedSuccess: '資金已成功新增',
    networkError: '網路錯誤',
    securityTokenNotLoaded: '安全權杖尚未載入。請稍候再試。',
    enterRecipientAndAmount: '請輸入收款人和有效的正數金額',
    insufficientBalance: '餘額不足',
    transferUnexpected: '轉帳已完成，但回應格式不符合預期。請重新整理頁面。',
    networkErrorWithMessage: '網路錯誤: {message}',
    serverErrorWithMessage: '伺服器錯誤: {message}',
    withdrawalExceedsBalance: '提款金額超過您的可用餘額。',
    validSixDigitCode: '請輸入有效的 6 位數代碼。',
    withdrawalFailed: '提款失敗。',
    invalidOrExpiredCode: '代碼無效或已過期。',
    networkErrorTryAgain: '網路錯誤。請再試一次。',
    withdrawLoadingDescription: '正在驗證您的代碼並準備轉帳請求。',
    withdrawLoadingCodeCheck: '代碼檢查',
    withdrawLoadingSecureQueue: '安全佇列',
    withdrawLoadingStatusUpdate: '狀態更新'
  },
  ar: {
    addMoneyTitle: 'إضافة أموال',
    addMoneySubtitle: 'أودع أموالاً في حسابك',
    enterAmount: 'أدخل المبلغ',
    selectPaymentMethod: 'اختر طريقة الدفع',
    cardOption: 'بطاقة',
    bankTransferOption: 'تحويل بنكي',
    addMoneyButton: 'إضافة أموال',
    cancelButton: 'إلغاء',
    processing: 'جار المعالجة...',
    pleaseSelectPaymentMethod: 'يرجى اختيار طريقة دفع',
    enterPositiveAmount: 'أدخل مبلغاً موجباً صالحاً',
    maxAmount: 'الحد الأقصى لكل معاملة هو $10,000',
    validCardNumber: 'يرجى إدخال رقم بطاقة صالح',
    validExpiryDate: 'يرجى إدخال تاريخ انتهاء صالح (MM/YY)',
    validCvv: 'يرجى إدخال CVV صالح',
    cardholderName: 'يرجى إدخال اسم حامل البطاقة',
    moneyAddedSuccess: 'تمت إضافة الأموال بنجاح',
    networkError: 'خطأ في الشبكة',
    securityTokenNotLoaded: 'لم يتم تحميل رمز الأمان بعد. يرجى الانتظار قليلاً ثم المحاولة مرة أخرى.',
    enterRecipientAndAmount: 'أدخل المستلم ومبلغاً موجباً صالحاً',
    insufficientBalance: 'الرصيد غير كاف',
    transferUnexpected: 'اكتمل التحويل ولكن تنسيق الاستجابة غير متوقع. يرجى تحديث الصفحة.',
    networkErrorWithMessage: 'خطأ في الشبكة: {message}',
    serverErrorWithMessage: 'خطأ في الخادم: {message}',
    withdrawalExceedsBalance: 'مبلغ السحب يتجاوز رصيدك المتاح.',
    validSixDigitCode: 'يرجى إدخال رمز صالح مكون من 6 أرقام.',
    withdrawalFailed: 'فشل السحب.',
    invalidOrExpiredCode: 'الرمز غير صالح أو منتهي الصلاحية.',
    networkErrorTryAgain: 'خطأ في الشبكة. يرجى المحاولة مرة أخرى.',
    withdrawLoadingDescription: 'جار التحقق من الرمز الخاص بك وتجهيز طلب التحويل.',
    withdrawLoadingCodeCheck: 'فحص الرمز',
    withdrawLoadingSecureQueue: 'قائمة آمنة',
    withdrawLoadingStatusUpdate: 'تحديث الحالة'
  },
  hi: {
    addMoneyTitle: 'पैसे जोड़ें',
    addMoneySubtitle: 'अपने खाते में धन जमा करें',
    enterAmount: 'राशि दर्ज करें',
    selectPaymentMethod: 'भुगतान विधि चुनें',
    cardOption: 'कार्ड',
    bankTransferOption: 'बैंक ट्रांसफर',
    addMoneyButton: 'पैसे जोड़ें',
    cancelButton: 'रद्द करें',
    processing: 'प्रोसेस हो रहा है...',
    pleaseSelectPaymentMethod: 'कृपया भुगतान विधि चुनें',
    enterPositiveAmount: 'मान्य धनात्मक राशि दर्ज करें',
    maxAmount: 'प्रति लेनदेन अधिकतम राशि $10,000 है',
    validCardNumber: 'कृपया मान्य कार्ड नंबर दर्ज करें',
    validExpiryDate: 'कृपया मान्य समाप्ति तिथि दर्ज करें (MM/YY)',
    validCvv: 'कृपया मान्य CVV दर्ज करें',
    cardholderName: 'कृपया कार्डधारक का नाम दर्ज करें',
    moneyAddedSuccess: 'पैसे सफलतापूर्वक जोड़ दिए गए',
    networkError: 'नेटवर्क त्रुटि',
    securityTokenNotLoaded: 'सुरक्षा टोकन अभी लोड नहीं हुआ है। कृपया थोड़ी देर प्रतीक्षा करें और फिर से प्रयास करें।',
    enterRecipientAndAmount: 'प्राप्तकर्ता और मान्य धनात्मक राशि दर्ज करें',
    insufficientBalance: 'अपर्याप्त शेष राशि',
    transferUnexpected: 'ट्रांसफर पूरा हो गया, लेकिन प्रतिक्रिया प्रारूप अप्रत्याशित था। कृपया पेज रीफ्रेश करें।',
    networkErrorWithMessage: 'नेटवर्क त्रुटि: {message}',
    serverErrorWithMessage: 'सर्वर त्रुटि: {message}',
    withdrawalExceedsBalance: 'निकासी राशि आपकी उपलब्ध शेष राशि से अधिक है।',
    validSixDigitCode: 'कृपया मान्य 6-अंकीय कोड दर्ज करें।',
    withdrawalFailed: 'निकासी विफल रही।',
    invalidOrExpiredCode: 'कोड अमान्य है या समाप्त हो चुका है।',
    networkErrorTryAgain: 'नेटवर्क त्रुटि। कृपया फिर से प्रयास करें।',
    withdrawLoadingDescription: 'आपके कोड का सत्यापन किया जा रहा है और ट्रांसफर अनुरोध तैयार किया जा रहा है।',
    withdrawLoadingCodeCheck: 'कोड जांच',
    withdrawLoadingSecureQueue: 'सुरक्षित कतार',
    withdrawLoadingStatusUpdate: 'स्थिति अपडेट'
  },
  ru: {
    addMoneyTitle: 'Пополнить счет',
    addMoneySubtitle: 'Внесите средства на свой счет',
    enterAmount: 'Введите сумму',
    selectPaymentMethod: 'Выберите способ оплаты',
    cardOption: 'Карта',
    bankTransferOption: 'Банковский перевод',
    addMoneyButton: 'Пополнить счет',
    cancelButton: 'Отмена',
    processing: 'Обработка...',
    pleaseSelectPaymentMethod: 'Пожалуйста, выберите способ оплаты',
    enterPositiveAmount: 'Введите корректную положительную сумму',
    maxAmount: 'Максимальная сумма за одну операцию составляет $10,000',
    validCardNumber: 'Пожалуйста, введите корректный номер карты',
    validExpiryDate: 'Пожалуйста, введите корректную дату окончания (MM/YY)',
    validCvv: 'Пожалуйста, введите корректный CVV',
    cardholderName: 'Пожалуйста, введите имя владельца карты',
    moneyAddedSuccess: 'Средства успешно добавлены',
    networkError: 'Ошибка сети',
    securityTokenNotLoaded: 'Токен безопасности еще не загружен. Подождите немного и попробуйте снова.',
    enterRecipientAndAmount: 'Введите получателя и корректную положительную сумму',
    insufficientBalance: 'Недостаточно средств',
    transferUnexpected: 'Перевод завершен, но формат ответа оказался неожиданным. Обновите страницу.',
    networkErrorWithMessage: 'Ошибка сети: {message}',
    serverErrorWithMessage: 'Ошибка сервера: {message}',
    withdrawalExceedsBalance: 'Сумма вывода превышает доступный баланс.',
    validSixDigitCode: 'Пожалуйста, введите корректный 6-значный код.',
    withdrawalFailed: 'Не удалось выполнить вывод.',
    invalidOrExpiredCode: 'Код недействителен или срок его действия истек.',
    networkErrorTryAgain: 'Ошибка сети. Попробуйте еще раз.',
    withdrawLoadingDescription: 'Проверяем ваш код и подготавливаем запрос на перевод.',
    withdrawLoadingCodeCheck: 'Проверка кода',
    withdrawLoadingSecureQueue: 'Безопасная очередь',
    withdrawLoadingStatusUpdate: 'Обновление статуса'
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
    paymentMethod.options[1].textContent = `💳 ${getBankUiText('cardOption')}`;
    paymentMethod.options[2].textContent = `🏦 ${getBankUiText('bankTransferOption')}`;
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
          });
      });
      if (withdrawBackBtn) {
        withdrawBackBtn.addEventListener('click', function() {
          withdrawCodeForm.style.display = 'none';
          withdrawForm.style.display = 'block';
        });
      }
      withdrawCodeForm.addEventListener('submit', function(e) {
        e.preventDefault();
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
            <div style="font-size:44px;margin-bottom:12px;">💸</div>
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
    USD: '$', GBP: '£', EUR: '€', CAD: 'C$', AUD: 'A$', JPY: '¥', CHF: 'Fr.',
    CNY: '¥', INR: '₹', MXN: '$', BRL: 'R$', ZAR: 'R', SGD: 'S$', HKD: 'HK$',
    KRW: '₩', TWD: 'NT$', THB: '฿', MYR: 'RM', IDR: 'Rp', PHP: '₱'
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
          let symbolMap = {USD: '$', GBP: '£', EUR: '€', CAD: 'C$', AUD: 'A$', JPY: '¥', CHF: 'Fr.', CNY: '¥', INR: '₹', MXN: '$', BRL: 'R$', ZAR: 'R', SGD: 'S$', HKD: 'HK$', KRW: '₩', TWD: 'NT$', THB: '฿', MYR: 'RM', IDR: 'Rp', PHP: '₱'};
          let currency = (window.state && window.state.currency) ? window.state.currency : 'USD';
          let symbol = symbolMap[currency] || '$';
          if (!card) {
            card = document.createElement('div');
            card.className = 'add-money-card';
            card.style.cssText = `background:linear-gradient(135deg,#f0f9ff 0%,#fff 100%);border-radius:32px;padding:0;max-width:440px;width:95vw;box-shadow:0 40px 100px rgba(2,132,199,0.18),0 0 0 1px rgba(2,132,199,0.08);overflow:hidden;position:relative;`;
            card.innerHTML = `
              <div style=\"background:linear-gradient(135deg,#0284c7 0%,#38bdf8 100%);padding:36px 24px 24px 24px;text-align:center;position:relative;overflow:hidden;\">
                <div style=\"position:absolute;top:-40px;left:-40px;width:120px;height:120px;background:radial-gradient(circle,rgba(255,255,255,0.18) 0%,transparent 80%);\"></div>
                <div style=\"font-size:60px;margin-bottom:12px;\">💰</div>
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
                  <option value="card">💳 ${getBankUiText('cardOption')}</option>
                  <option value="bank">🏦 ${getBankUiText('bankTransferOption')}</option>
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
              <div style="font-size:60px;margin-bottom:12px;">🎉</div>
              <h2 style="font-size:clamp(24px,5vw,32px);font-weight:900;color:#fff;margin:0 0 8px 0;letter-spacing:-0.5px;text-shadow:0 4px 12px rgba(2,132,199,0.18);">${translateUi('transfer-successful-title', 'Transfer Successful!')}</h2>
              <div style="color:#bae6fd;font-size:clamp(13px,3vw,15px);font-weight:500;">${translateUi('payment-processed', 'Your payment has been processed')}</div>
            </div>
            <div style="padding:clamp(24px,5vw,36px);position:relative;">
              <div style="background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);border-radius:20px;border:2px solid #86efac;box-shadow:0 8px 32px rgba(134,239,172,0.12);padding:20px 0 12px 0;margin-bottom:24px;">
                <div style="color:#166534;font-size:clamp(12px,2.8vw,14px);font-weight:700;margin-bottom:10px;text-transform:uppercase;letter-spacing:1px">💰 ${translateUi('amount-sent-label', 'Amount Sent')}</div>
                <div style="color:#166534;font-weight:900;font-size:clamp(38px,9vw,48px);letter-spacing:-2px;text-shadow:0 2px 4px rgba(22,101,52,0.1);margin-bottom:8px">$${parseFloat(amount).toFixed(2)}</div>
              </div>
              <div style="margin-bottom:8px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">👤 <span>${translateUi('sender-label', 'Sender')}:</span> <span style="font-weight:700">${senderName}</span></div>
              <div style="margin-bottom:18px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">📤 <span>${translateUi('to-label', 'To')}:</span> <span style="font-weight:700">${recipient}</span></div>
              <div style="margin-bottom:8px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">📅 <span>${translateUi('date-label', 'Date')}:</span> <span style="font-weight:700">${currentDate}</span></div>
              <div style="margin-bottom:8px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">⏰ <span>${translateUi('time-label', 'Time')}:</span> <span style="font-weight:700">${currentTime}</span></div>
              <div style="margin-bottom:18px;font-size:15px;color:#374151;display:flex;align-items:center;gap:8px;justify-content:center;">🆔 <span>${translateUi('transaction-id-label', 'Transaction ID')}:</span> <span style="font-weight:700">${transactionId}</span></div>
              <div style="margin-bottom:18px;font-size:15px;color:#22c55e;font-weight:700;text-align:center;">✅ ${translateUi('status-label', 'Status')}: ${translateUi('status-completed', 'Completed')}</div>
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
          bankNameDiv.innerHTML = '🏦 ' + parts.slice(1).join(' ');
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
    '<p style="font-size:clamp(14px,3.5vw,16px);color:rgba(255,255,255,0.95);margin:0;font-weight:500;animation:slideInFromTop 0.6s ease 0.4s both">🎉 ' + translateUi('payment-processed', 'Your payment has been processed') + '</p>' +
    '</div>' +
    
    // Main content
    '<div style="padding:clamp(24px,5vw,36px);position:relative">' +
    
    // Amount display - Enhanced with animation
    '<div style="text-align:center;margin-bottom:28px;padding:24px;background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);border-radius:20px;border:2px solid #86efac;box-shadow:0 8px 32px rgba(134,239,172,0.2);position:relative;overflow:hidden">' +
    '<div style="position:absolute;top:0;left:0;right:0;height:100%;background:linear-gradient(45deg,transparent 48%,rgba(255,255,255,0.5) 50%,transparent 52%);background-size:200% 200%;animation:shimmer 3s linear infinite"></div>' +
    '<div style="color:#166534;font-size:clamp(12px,2.8vw,14px);font-weight:700;margin-bottom:12px;text-transform:uppercase;letter-spacing:1px">💸 ' + translateUi('amount-sent-label', 'Amount Sent') + '</div>' +
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
    '<p style="color:#94a3b8;font-size:clamp(11px,2.5vw,12px);margin:0;font-weight:500">🔒 ' + translateUi('transaction-secure', 'This transaction is secure and encrypted') + '</p>' +
    '</div>' +
    
    '</div>';
  
  modal.appendChild(card);
	  document.body.appendChild(modal);


}
  
  // Add share functionality
  const shareBtn = document.getElementById('share-receipt-btn');
  if (shareBtn) {
    shareBtn.addEventListener('click', function() {
      const receiptText = `🎉 ${translateUi('transfer-successful-title', 'Transfer Successful!')}\n\n💰 ${translateUi('amount-label', 'Amount')}: $${parseFloat(amount).toFixed(2)}\n📤 ${translateUi('to-label', 'To')}: ${recipient}\n📅 ${translateUi('date-label', 'Date')}: ${currentDate}\n⏰ ${translateUi('time-label', 'Time')}: ${currentTime}\n🆔 ${translateUi('transaction-id-label', 'Transaction ID')}: ${transactionId}\n\n✅ ${translateUi('status-label', 'Status')}: ${translateUi('status-completed', 'Completed')}\n\n${translateUi('sent-via-wallet', 'Sent via Mivonta')}`;
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
