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

            {l s='Es gelten die Filter aus den Moduleinstellungen. Negative Rechnungsbeträge bleiben beim Ausschluss von Nullbeträgen enthalten.' mod='wisoexport'}

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