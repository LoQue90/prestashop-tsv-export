<div class="panel">

    <div class="panel-heading">
        <i class="icon-file-text"></i>
        {l s='WISO EÜR Export' mod='wisoexport'}
    </div>


    <form method="post">


        <div class="form-group">
            <label>
                {l s='Von Datum' mod='wisoexport'}
            </label>

            <input
                type="date"
                name="date_from"
                class="form-control"
                required
            >
        </div>



        <div class="form-group">
            <label>
                {l s='Bis Datum' mod='wisoexport'}
            </label>

            <input
                type="date"
                name="date_to"
                class="form-control"
                required
            >
        </div>



        <div class="alert alert-info">

            <strong>{l s='Exportfilter' mod='wisoexport'}</strong>
            <ul>
                <li>
                    {l s='Bestellstatus:' mod='wisoexport'}
                    {if $wiso_use_status_filter}
                        {if $wiso_status_name}
                            {$wiso_status_name|escape:'html':'UTF-8'}
                        {else}
                            {l s='Kein Bestellstatus ausgewählt. Bitte die Moduleinstellungen prüfen.' mod='wisoexport'}
                        {/if}
                    {else}
                        {l s='Alle Bestellstatus' mod='wisoexport'}
                    {/if}
                </li>
                <li>
                    {l s='Nullbeträge:' mod='wisoexport'}
                    {if $wiso_exclude_zero}
                        {l s='ausgeschlossen' mod='wisoexport'}
                    {else}
                        {l s='enthalten' mod='wisoexport'}
                    {/if}
                </li>
                <li>{l s='Negative Rechnungsbeträge: enthalten' mod='wisoexport'}</li>
            </ul>

        </div>



        <button
            type="submit"
            name="submitWisoExport"
            value="1"
            class="btn btn-primary"
        >

            <i class="icon-download"></i>

            {l s='TSV Export erstellen' mod='wisoexport'}

        </button>


    </form>

</div>