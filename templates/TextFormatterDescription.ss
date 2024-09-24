<dialog id="dialog-{$ID}">
    <p>{$Description}</p>
    <ul>
        <% loop $Tags %>
            <li>
                <strong>{$Title}:</strong>
                <br />
                <span>$Description</span>
                <br />
                <% if $ClosingTag %>
                    Öffnender Tag: {$OpeningTag}
                    <br />
                    Schließender Tag: {$ClosingTag}
                <% else %>
                    Tag: {$OpeningTag}
                    <br />
                <% end_if %>
            </li>
        <% end_loop %>
    </ul>
    <button class="btn btn-outline-primary" data-close-id="{$ID}" autofocus>Schließen</button>
</dialog>
<p class="mt-2">Die folgenden Formatierungen können im Text verwendet werden:</p>
<button class="btn btn-outline-primary" data-show-id="{$ID}">Formatierungen anzeigen</button>
<script>
    document.querySelector('button[data-show-id="{$ID}"]').addEventListener("click", () => {
        document.getElementById("dialog-{$ID}").showModal();
    });

    document.querySelector('button[data-close-id="{$ID}"]').addEventListener("click", () => {
        document.getElementById("dialog-{$ID}").close();
    });
</script>
