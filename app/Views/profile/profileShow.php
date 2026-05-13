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
          <div style="width:6rem;height:6rem;border-radius:9999px;overflow:hidden;flex-shrink:0;">
            <img src="<?= esc($user['avatar']) ?>" alt="Avatar de <?= esc($user['firstname']) ?>" style="width:100%;height:100%;object-fit:cover;"
              onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
          </div>
          <div style="display:none;width:6rem;height:6rem;border-radius:9999px;background:#C85028;color:#EFEAE0;font-weight:700;font-size:1.5rem;flex-shrink:0;align-items:center;justify-content:center;">
            <?= $initials ?>
          </div>
        <?php else: ?>
          <div style="display:flex;width:6rem;height:6rem;border-radius:9999px;background:#C85028;color:#EFEAE0;font-weight:700;font-size:1.5rem;flex-shrink:0;align-items:center;justify-content:center;">
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
          Membre depuis <?= date('F Y', strtotime($user['registered_at'])) ?>
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
      <a href="<?= site_url('profile/profileEdit') ?>">
        <i class="fa-solid fa-pen"></i>
        Modifier
      </a>
    <?php endif; ?>

  </div>
</div>

<?= view('partials/footer') ?>
