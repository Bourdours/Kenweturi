<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div>


  <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-success/10 border-b border-success/20 px-6 py-3 text-success text-sm text-center">
      <?= session()->getFlashdata('success') ?>
    </div>
  <?php endif; ?>
  <div>

    <div>

      <!-- Avatar -->
      <div>
        <?php $initials = strtoupper(substr($user['firstname'], 0, 1) . substr($user['lastname'], 0, 1)); ?>
        <?php if (!empty($user['avatar'])): ?>
          <div class="jsAvatarOpen" style="width:6rem;height:6rem;border-radius:9999px;overflow:hidden;flex-shrink:0;cursor:pointer;">
            <img src="<?= esc($user['avatar']) ?>" alt="Avatar de <?= esc($user['firstname']) ?>" style="width:100%;height:100%;object-fit:cover;"
              onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
          </div>
          <div class="jsAvatarOpen" style="display:none;width:6rem;height:6rem;border-radius:9999px;background:#C85028;color:#EFEAE0;font-weight:700;font-size:1.5rem;flex-shrink:0;align-items:center;justify-content:center;cursor:pointer;">
            <?= $initials ?>
          </div>
        <?php else: ?>
          <div class="jsAvatarOpen" style="display:flex;width:6rem;height:6rem;border-radius:9999px;background:#C85028;color:#EFEAE0;font-weight:700;font-size:1.5rem;flex-shrink:0;align-items:center;justify-content:center;cursor:pointer;">
            <?= $initials ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Nom + badges + ville -->
      <div>
        <div>
          <h1><?= esc($user['firstname']) ?> <?= esc($user['lastname']) ?></h1>
          <?php if ($user['is_student']): ?>
            <span>Étudiant</span>
          <?php endif; ?>
        </div>

        <p>
          <?php if (!empty($city)): ?>
            <i class="fa-solid fa-location-dot"></i>
            <?= esc($city) ?>
            ·
          <?php endif; ?>
          <i class="fa-regular fa-calendar"></i>
          Membre depuis <?= esc($memberSince) ?>
        </p>
      </div>

    </div>



    <!-- Bio -->
    <div>
      <h2>À propos</h2>
      <?php if (!empty($user['biography'])): ?>
        <p><?= esc($user['biography']) ?></p>
      <?php else: ?>
        <p>
          <?= $isOwnProfile ? 'Vous n\'avez pas encore ajouté de bio.' : 'Aucune bio renseignée.' ?>
        </p>
      <?php endif; ?>
    </div>

    <!-- Infos personnelles -->
    <div>
      <h2>Informations</h2>
      <dl>

        <div>
          <i class="fa-solid fa-envelope"></i>
          <div>
            <dt>Email</dt>
            <dd><?= esc($user['email']) ?></dd>
          </div>
        </div>

        <div>
          <i class="fa-solid fa-venus-mars"></i>
          <div>
            <dt>Genre</dt>
            <dd><?= esc($user['gender']) ?></dd>
          </div>
        </div>

        <div>
          <i class="fa-solid fa-cake-candles"></i>
          <div>
            <dt>Âge</dt>
            <dd>
              <?php
              $birth = new DateTime($user['birth_date']);
              $age   = (new DateTime())->diff($birth)->y;
              echo $age . ' ans';
              ?>
            </dd>
          </div>
        </div>

        <?php if (!empty($city)): ?>
          <div>
            <i class="fa-solid fa-location-dot"></i>
            <div>
              <dt>Ville</dt>
              <dd><?= esc($city) ?></dd>
            </div>
          </div>
        <?php endif; ?>

      </dl>
    </div>

    <!-- Bouton modifier -->
    <?php if ($isOwnProfile): ?>
      <a href="<?= site_url('profile/update') ?>">
        <i class="fa-solid fa-pen"></i>
        Modifier
      </a>

        <!-- Suppression du compte -->
      <form action="<?= site_url('profile/delete') ?>" method="post" class="deleteAccount">
        <?= csrf_field() ?>
        <button type="submit">
          <i class="fa-solid fa-trash"></i>
          Supprimer mon compte
        </button>
      </form>
    <?php endif; ?>

  </div>
</div>

<!-- Modal avatar -->
<div id="avatarModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.8);z-index:9999;align-items:center;justify-content:center;cursor:zoom-out;">
  <?php if (!empty($user['avatar'])): ?>
    <img src="<?= esc($user['avatar']) ?>" alt="Avatar de <?= esc($user['firstname']) ?>" style="max-width:90vw;max-height:90vh;border-radius:0.5rem;object-fit:contain;box-shadow:0 0 40px rgba(0,0,0,0.5);"
      onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
    <div style="display:none;width:16rem;height:16rem;border-radius:9999px;background:#C85028;color:#EFEAE0;font-weight:700;font-size:5rem;align-items:center;justify-content:center;box-shadow:0 0 40px rgba(0,0,0,0.5);">
      <?= $initials ?>
    </div>
  <?php else: ?>
    <div style="width:16rem;height:16rem;border-radius:9999px;background:#C85028;color:#EFEAE0;font-weight:700;font-size:5rem;display:flex;align-items:center;justify-content:center;box-shadow:0 0 40px rgba(0,0,0,0.5);">
      <?= $initials ?>
    </div>
  <?php endif; ?>
</div>

<script src="<?= base_url('js/user.js') ?>"></script>

<?= view('partials/footer') ?>