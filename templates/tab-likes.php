              <section class="profile__likes tabs__content tabs__content--active">
                <h2 class="visually-hidden">Лайки</h2>
                <ul class="profile__likes-list">
                <?php foreach ($posts as $post): ?>
                  <li class="post-mini post-mini--<?=$post['p_type'];?> post user">
                    <div class="post-mini__user-info user__info">
                      <div class="post-mini__avatar user__avatar">
                        <a class="user__avatar-link" href="/profile.php?id=<?=$post['l_user'];?>">
                          <?php if ($post['u_avatar']): ?>
                          <img class="post-mini__picture user__picture" src="<?=$post['u_avatar'];?>" width="60" heigth="60" alt="Аватар пользователя">
                          <?php endif; ?>
                        </a>
                      </div>
                      <div class="post-mini__name-wrapper user__name-wrapper">
                        <a class="post-mini__name user__name" href="/profile.php?id=<?=$post['l_user'];?>">
                          <span><?=$post['u_name']?></span>
                        </a>
                        <div class="post-mini__action">
                          <span class="post-mini__activity user__additional">Лайкнул вашу публикацию</span>
                          <time class="post-mini__time user__additional" datetime="<?=$post['l_dt'];?>"><?= generate_passed_time_text($post['l_dt']);?> назад</time>
                        </div>
                      </div>
                    </div>
                    <div class="post-mini__preview">
                      <a class="post-mini__link" href="/post.php?id=<?=$post['id'];?>" title="Перейти на публикацию">
                      <!-- Разные типы постов -->
                      <?php switch($post['p_type']):
                      case 'photo': ?>
                      <!-- Фото -->
                        <div class="post-mini__image-wrapper">
                          <img class="post-mini__image" src="<?=$post['p_url'];?>" width="109" height="109" alt="Превью публикации">
                        </div>
                        <span class="visually-hidden">Фото</span>
                      <?php break;
                      case 'video': ?>
                      <!-- Видео -->
                        <div class="post-mini__image-wrapper">
                          <?=embed_youtube_cover($post['p_url']); ?>
                          <span class="post-mini__play-big">
                            <svg class="post-mini__play-big-icon" width="12" height="13">
                              <use xlink:href="#icon-video-play-big"></use>
                            </svg>
                          </span>
                        </div>
                        <span class="visually-hidden">Видео</span>
                      <?php break;
                      default: ?>
                      <!-- Остальные типы постов -->
                        <svg class="post-mini__preview-icon" width="<?=$post['width'];?>" height="<?=$post['height'];?>">
                          <use xlink:href="#icon-filter-<?=$post['p_type']?>"></use>
                        </svg>
                      <?php endswitch; ?>
                        <span class="visually-hidden"><?=$post['t_title'];?></span>
                      </a>
                    </div>
                  </li>
                  <?php endforeach; ?>
                </ul>
              </section>
