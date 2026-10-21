
<div id="scrollBar"></div>

<button id="toTop" type="button" aria-label="Back to top">
    <svg viewBox="0 0 50 50"><circle class="trk" cx="25" cy="25" r="22"/><circle class="bar" cx="25" cy="25" r="22"/></svg>
    <i class="bi bi-arrow-up"></i>
</button>

<div id="cfm" class="cfm" role="dialog" aria-modal="true" aria-labelledby="cfmTitle" aria-hidden="true">
    <div class="cfm-card">
        <div class="cfm-icon"><i class="bi bi-trash3" id="cfmIcon"></i></div>
        <h5 id="cfmTitle">Are you sure?</h5>
        <p id="cfmText"></p>
        <div class="cfm-actions">
            <button type="button" class="cfm-btn cancel" id="cfmCancel">Cancel</button>
            <button type="button" class="cfm-btn ok" id="cfmOk">Confirm</button>
        </div>
    </div>
</div>