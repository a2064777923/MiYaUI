<?php
/**
 * 话题自定义文章类型的单页模板
 *
 * @package XHTheme_AI_Toolbox
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables with xhai_ prefix

get_header();

// 获取父文章信息
$xhai_current_id = get_the_ID();
$xhai_parent_id = get_post_meta($xhai_current_id, 'xhai_postparent', true);
$xhai_parent_post = $xhai_parent_id ? get_post($xhai_parent_id) : null;
?>

<div class="content-area xhaitool-thread-container xhaitool-thread-c2">
  <main id="main" class="site-main">
    <article id="post-<?php the_ID(); ?>" <?php post_class('xhtheme-thread-single'); ?>>
      <header class="entry-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>

        <?php if ($xhai_parent_post): ?>
          <div class="thread-parent-post">
            <p><?php esc_html_e('Thread Source:', 'xhtheme-ai-toolbox'); ?>
              <a
                href="<?php echo esc_url(get_permalink($xhai_parent_id)); ?>"><?php echo esc_html($xhai_parent_post->post_title); ?></a>
            </p>
          </div>
        <?php endif; ?>
      </header>

      <div class="entry-content">
        <?php the_content(); ?>
      </div>

      <?php if ($xhai_parent_post): ?>
        <footer class="entry-footer">
          <?php
          $xhai_thread_desc = XHTheme\AIToolbox\xh_option('primaryThreadDesc');
          if ($xhai_thread_desc) {
            printf('<p style="opacity:.5;font-size:.85em">%s</p>', wp_kses_post($xhai_thread_desc));
          }
          ?>
          <div class="thread-back-link">
            <a href="<?php the_permalink($xhai_parent_id); ?>">
              <?php esc_html_e('Back to Original Post', 'xhtheme-ai-toolbox'); ?>
            </a>
          </div>
        </footer>
      <?php endif; ?>
    </article>

    <?php
    $xhai_related_threads = XHTheme\AIToolbox\XHThread::get_related_threads($xhai_current_id, 5);
    if (!empty($xhai_related_threads)):
      ?>
      <div class="recommended-threads-section">
        <h3 class="recommended-section-title"><?php esc_html_e('Recommended Threads', 'xhtheme-ai-toolbox'); ?></h3>
        <ul class="recommended-threads-list">
          <?php foreach ($xhai_related_threads as $xhai_thread):
            $xhai_comment_count = get_comments_number($xhai_thread->ID);
            ?>
            <li class="recommended-thread-item">
              <a href="<?php echo esc_url(get_permalink($xhai_thread->ID)); ?>" class="thread-title">
                <?php echo esc_html($xhai_thread->post_title); ?>
              </a>
              <span class="thread-comment-count">
                <?php
                printf(
                  // translators: %s is the number of participants in the thread.
                  esc_html(_n('%s participant', '%s participants', $xhai_comment_count, 'xhtheme-ai-toolbox')),
                  esc_html(number_format_i18n($xhai_comment_count))
                );
                ?>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php
    // Add comments functionality
    if (comments_open() || get_comments_number()) {
      echo '<div class="xhthread-comments-section">';
      /**
       * 适配子比主题
       */
      if (function_exists('zib_require')) {
        comments_template('/template/comments.php', true);
      }

      /**
       * 调用默认评论模板
       */ else {
        comments_template();
      }
      echo '</div>';
    }
    ?>
  </main>
</div>



<?php
get_footer();