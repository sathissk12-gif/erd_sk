/**
 * C:\Users\sathi\.gemini\antigravity\scratch\billing_app\Migration.gs
 * Copy this code into your Google Apps Script Editor
 */

function migrateToMySQL() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var apiUrl = "https://erd.traxengps.in/billing_app/import_api.php"; // UPDATE IF NEEDED
  
  // Mapping of Google Sheet Name => Database Table Name
  var tablesToMigrate = {
    "SALES_LOG": "sales_log",
    "INVOICE_LOG": "invoice_log",
    "RENEWAL_LOG": "renewal_log",
    "RENEWAL_INVOICE_LOG": "renewal_invoice_log",
    "DEVICE_MASTER": "device_master",
    "DEALER_LEDGER": "dealer_ledger",
    "PRICE_MASTER": "price_master",
    "DEALER_RATE_MASTER": "dealer_rate_master",
    "SOFTWARE_MASTER": "software_master",
    "STOCK_LEDGER": "stock_ledger",
    "SETTINGS": "settings",
    "SIM_SETTLEMENT": "sim_settlement",
    "OFFICE_SALES": "office_sales",
    "OFFICE_RENEWAL": "office_renewal"
  };

  var ui = SpreadsheetApp.getUi();

  for (var sheetName in tablesToMigrate) {
    var tableName = tablesToMigrate[sheetName];
    var sheet = ss.getSheetByName(sheetName);
    
    if (!sheet) {
      Logger.log("Skipping: Sheet '" + sheetName + "' not found.");
      continue;
    }
    
    var data = sheet.getDataRange().getValues();
    if (data.length < 2) {
      Logger.log("Skipping: " + sheetName + " has no data.");
      continue;
    }

    var headers = data[0]; 
    var rows = data.slice(1); 
    
    var jsonData = rows.map(function(r) {
      var obj = {};
      headers.forEach(function(h, i) {
        // CLEAN HEADERS: Remove newlines, trim, lowercase, replace spaces with underscores
        var key = h.toString().trim().toLowerCase().replace(/\s+/g, "_");
        
        var val = r[i];
        // Handle Dates correctly for MySQL (YYYY-MM-DD)
        if (val instanceof Date) {
           val = Utilities.formatDate(val, ss.getSpreadsheetTimeZone(), "yyyy-MM-dd");
        }
        obj[key] = val;
      });
      return obj;
    });

    // Chunk data if too large (Optional, but safe for 1000+ rows)
    var payload = JSON.stringify({
      'table': tableName,
      'data': jsonData
    });

    var options = {
      'method': 'post',
      'contentType': 'application/json',
      'payload': payload,
      'muteHttpExceptions': true
    };
    
    try {
      var response = UrlFetchApp.fetch(apiUrl, options);
      var result = JSON.parse(response.getContentText());
      Logger.log(sheetName + " Status: " + result.status + " | " + result.message);
    } catch(e) {
      Logger.log("Critical Error in " + sheetName + ": " + e.toString());
    }
  }
  
  ui.alert("Migration Process Finished. Check View > Logs to see detailed results.");
}

/**
 * Adds a custom menu to the spreadsheet to run the migration easily.
 */
function onOpen() {
  var ui = SpreadsheetApp.getUi();
  ui.createMenu('🚀 Admin Panel')
      .addItem('Move Data to MySQL', 'migrateToMySQL')
      .addToUi();
}
