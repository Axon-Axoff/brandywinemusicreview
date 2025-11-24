<!-- Player Modal -->
<div class="modal fade" id="musicModal" tabindex="-1" aria-labelledby="musicModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark">
      <div class="modal-header border-0">
        <h5 class="modal-title text-light" id="musicModalLabel">Now Playing</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex justify-content-center">
        <iframe src="" width="100%" height="380" frameborder="0"
          allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const musicModal = document.getElementById('musicModal');
    if (!musicModal) return;

    const modalBody = musicModal.querySelector('.modal-body');
    const defaultIframe = `<iframe src="" width="100%" height="380" frameborder="0"
    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>`;
    let iframe = modalBody.querySelector('iframe');

    musicModal.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget;
      const url = button.getAttribute('data-music');
      if (!url) return;

      // Reset
      iframe.src = "";

      if (url.includes("bandcamp.com")) {
        if (url.startsWith("<iframe")) {
          // Insert the full Bandcamp embed iframe
          modalBody.innerHTML = url;
        } else {
          // Fallback - open Bandcamp page in a new tab
          const modalInstance = bootstrap.Modal.getInstance(musicModal);
          modalInstance.hide();
          window.open(url, "_blank");
        }
      } else {
        // Spotify / YouTube (already normalized on backend)
        iframe.src = url;
      }
    });

    musicModal.addEventListener('hidden.bs.modal', () => {
      // Clear Bandcamp embed and restore default iframe to stop playback
      modalBody.innerHTML = defaultIframe;
      iframe = modalBody.querySelector('iframe');
    });
  });
</script>