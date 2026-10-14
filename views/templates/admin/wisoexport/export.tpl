{literal}<style>
.wiso-dates { display:flex; flex-wrap:wrap; gap:16px; }
#wiso-export .wiso-date-field, #wiso-export .wiso-date-field input { width:180px; max-width:100%; }
.wiso-invoice-choice { max-width:520px; }
@media (max-width:480px) { .wiso-dates { flex-direction:column; } }
</style>{/literal}
<div class="panel" id="wiso-export">
    <div class="panel-heading"><i class="icon-file-text"></i> {l s='WISO EÜR Export' mod='wisoexport'}</div>
    <form method="post" action="{$wiso_url|escape:'html':'UTF-8'}">
        <input type="hidden" name="token" value="{$wiso_token|escape:'html':'UTF-8'}">
        <h3>{l s='Exportbereich' mod='wisoexport'}</h3>
        <div class="radio"><label><input type="radio" name="export_mode" value="dates"{if $wiso_mode == 'dates'} checked{/if}> {l s='Zeitraum' mod='wisoexport'}</label></div>
        <div class="radio"><label><input type="radio" name="export_mode" value="invoice"{if $wiso_mode == 'invoice'} checked{/if}> {l s='Ab Rechnung (einschließlich)' mod='wisoexport'}</label></div>
        <div id="wiso-date-range" class="wiso-dates"{if $wiso_mode != 'dates'} style="display:none"{/if}>
            <div class="form-group wiso-date-field">
                <label for="wiso-from">{l s='Von Datum' mod='wisoexport'}</label>
                <input id="wiso-from" type="date" name="date_from" class="form-control" value="{$wiso_date_from|escape:'html':'UTF-8'}"{if $wiso_mode == 'dates'} required{else} disabled{/if}>
            </div>
            <div class="form-group wiso-date-field">
                <label for="wiso-to">{l s='Bis Datum' mod='wisoexport'}</label>
                <input id="wiso-to" type="date" name="date_to" class="form-control" value="{$wiso_date_to|escape:'html':'UTF-8'}"{if $wiso_mode == 'dates'} required{else} disabled{/if}>
            </div>
        </div>
        <div id="wiso-invoice-range" class="form-group wiso-invoice-choice"{if $wiso_mode != 'invoice'} style="display:none"{/if}>
            <label for="wiso-invoice-search">{l s='Rechnung suchen (Nummer oder Datum)' mod='wisoexport'}</label>
            <input id="wiso-invoice-search" type="search" class="form-control" autocomplete="off">
            <label for="wiso-start-invoice">{l s='Startrechnung auswählen' mod='wisoexport'}</label>
            <select id="wiso-start-invoice" name="start_invoice_id" class="form-control" size="6"{if $wiso_mode == 'invoice'} required{else} disabled{/if}>
                <option value="">{l s='Bitte eine Rechnung auswählen' mod='wisoexport'}</option>
                {foreach from=$wiso_choices item=choice}
                    <option value="{$choice.id|intval}"{if $choice.id == $wiso_start_id} selected{/if}>{$choice.label|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
            <p class="help-block">{l s='Die gewählte Rechnung und alle danach erstellten Rechnungen werden berücksichtigt. Die Filter unten gelten weiterhin.' mod='wisoexport'}</p>
        </div>
        <h3>{l s='Filter' mod='wisoexport'}</h3>
        <div class="checkbox">
            <label><input type="checkbox" name="WISOEXPORT_USE_STATUS_FILTER" value="1"{if $wiso_settings.WISOEXPORT_USE_STATUS_FILTER} checked{/if}> {l s='Bestellstatus verwenden' mod='wisoexport'}</label>
        </div>
        <div class="form-group">
            <label for="wiso-state">{l s='Bestellstatus für Export' mod='wisoexport'}</label>
            <select id="wiso-state" name="WISOEXPORT_ORDER_STATE" class="form-control">
                <option value="0">{l s='Bitte auswählen' mod='wisoexport'}</option>
                {foreach from=$wiso_states item=state}
                    <option value="{$state.id_order_state|intval}"{if $state.id_order_state == $wiso_settings.WISOEXPORT_ORDER_STATE} selected{/if}>{$state.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
            <p class="help-block">{l s='Ohne aktivierten Statusfilter werden alle Bestellstatus berücksichtigt.' mod='wisoexport'}</p>
        </div>
        <div class="checkbox">
            <label><input type="checkbox" name="WISOEXPORT_EXCLUDE_ZERO" value="1"{if $wiso_settings.WISOEXPORT_EXCLUDE_ZERO} checked{/if}> {l s='Rechnungen mit Betrag 0 ausschließen' mod='wisoexport'}</label>
        </div>
        <p class="help-block">{l s='Negative Rechnungsbeträge bleiben enthalten.' mod='wisoexport'}</p>
        <h3>{l s='Buchungsdaten' mod='wisoexport'}</h3>
        <div class="form-group">
            <label for="wiso-description">{l s='Bezeichnung' mod='wisoexport'}</label>
            <input id="wiso-description" type="text" name="WISOEXPORT_DESCRIPTION" maxlength="255" class="form-control" value="{$wiso_settings.WISOEXPORT_DESCRIPTION|escape:'html':'UTF-8'}">
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="wiso-account">{l s='Sachkonto' mod='wisoexport'}</label>
                <input id="wiso-account" type="text" name="WISOEXPORT_ACCOUNT" maxlength="255" class="form-control" value="{$wiso_settings.WISOEXPORT_ACCOUNT|escape:'html':'UTF-8'}">
            </div>
            <div class="form-group col-md-6">
                <label for="wiso-bank">{l s='Geldkonto' mod='wisoexport'}</label>
                <input id="wiso-bank" type="text" name="WISOEXPORT_BANK" maxlength="255" class="form-control" value="{$wiso_settings.WISOEXPORT_BANK|escape:'html':'UTF-8'}">
            </div>
        </div>
        <div class="form-group">
            <label for="wiso-tax">{l s='Umsatzsteuerart' mod='wisoexport'}</label>
            <select id="wiso-tax" name="WISOEXPORT_TAX_TYPE" class="form-control">
                <option value="NULL"{if $wiso_settings.WISOEXPORT_TAX_TYPE == 'NULL'} selected{/if}>{l s='Kleinunternehmer' mod='wisoexport'}</option>
                <option value="Brutto"{if $wiso_settings.WISOEXPORT_TAX_TYPE == 'Brutto'} selected{/if}>{l s='Brutto' mod='wisoexport'}</option>
                <option value="Netto"{if $wiso_settings.WISOEXPORT_TAX_TYPE == 'Netto'} selected{/if}>{l s='Netto' mod='wisoexport'}</option>
            </select>
        </div>
        <p class="help-block">{l s='Filter und Buchungsdaten werden beim Exportieren für den nächsten Export gespeichert.' mod='wisoexport'}</p>
        <button type="submit" name="submitWisoExport" value="1" class="btn btn-primary"><i class="icon-download"></i> {l s='TSV exportieren' mod='wisoexport'}</button>
    </form>
</div>
<div class="panel">
    <div class="panel-heading"><i class="icon-history"></i> {l s='Letzter Export' mod='wisoexport'}</div>
    {if $wiso_last}
        <dl class="dl-horizontal">
            <dt>{l s='Exportiert am' mod='wisoexport'}</dt><dd>{$wiso_last.created_display|escape:'html':'UTF-8'}</dd>
            {if $wiso_last.mode == 'invoice'}
                <dt>{l s='Ab Rechnung' mod='wisoexport'}</dt><dd>{$wiso_last.start_invoice_number|escape:'html':'UTF-8'} ({l s='einschließlich' mod='wisoexport'})</dd>
            {else}
                <dt>{l s='Zeitraum' mod='wisoexport'}</dt><dd>{$wiso_last.from_display|escape:'html':'UTF-8'} – {$wiso_last.to_display|escape:'html':'UTF-8'}</dd>
            {/if}
            <dt>{l s='Letzte Rechnungsnummer' mod='wisoexport'}</dt><dd>{$wiso_last.last_invoice_number|escape:'html':'UTF-8'}</dd>
            <dt>{l s='Anzahl Rechnungen' mod='wisoexport'}</dt><dd>{$wiso_last.count|intval}</dd>
        </dl>
    {else}
        <p>{l s='Noch kein Export erfasst.' mod='wisoexport'}</p>
    {/if}
    <p class="help-block">{l s='Zeigt die zuletzt erstellte Exportdatei, nicht den Import in WISO. Nach einem Download die Anzeige aktualisieren.' mod='wisoexport'}</p>
    <a class="btn btn-default" href="{$wiso_url|escape:'html':'UTF-8'}"><i class="icon-refresh"></i> {l s='Anzeige aktualisieren' mod='wisoexport'}</a>
</div>

{literal}<script>
(function () {
    var root = document.getElementById('wiso-export');
    var dates = document.getElementById('wiso-date-range');
    var invoices = document.getElementById('wiso-invoice-range');
    var select = document.getElementById('wiso-start-invoice');
    var search = document.getElementById('wiso-invoice-search');
    var options = Array.from(select.options, function (o) { return {value:o.value, label:o.text}; });
    function updateMode() {
        var byInvoice = root.querySelector('input[name="export_mode"]:checked').value === 'invoice';
        dates.style.display = byInvoice ? 'none' : '';
        invoices.style.display = byInvoice ? '' : 'none';
        dates.querySelectorAll('input').forEach(function (input) {
            input.disabled = byInvoice;
            input.required = !byInvoice;
        });
        select.disabled = !byInvoice;
        select.required = byInvoice;
    }
    root.querySelectorAll('input[name="export_mode"]').forEach(function (input) { input.addEventListener('change', updateMode); });
    search.addEventListener('input', function () {
        var query = search.value.trim().toLocaleLowerCase();
        var selected = select.value;
        select.innerHTML = '';
        options.forEach(function (option) {
            if (!option.value || option.label.toLocaleLowerCase().includes(query)) {
                var node = new Option(option.label, option.value);
                node.selected = option.value === selected;
                select.add(node);
            }
        });
        if (!Array.from(select.options).some(function (o) { return o.value === selected; })) { select.value = ''; }
    });
    updateMode();
}());
</script>{/literal}
