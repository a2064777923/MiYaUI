<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","align":"full","className":"xhaitool-thread-c1","layout":{"type":"default"},"style":{"spacing":{"padding":{"top":"0","bottom":"0"},"margin":{"top":"0","bottom":"0"}}}} -->
<main class="wp-block-group alignfull xhaitool-thread-c1"
    style="margin-top:0;margin-bottom:0;padding-top:0;padding-bottom:0">

    <!-- wp:group {"className":"thread-header","layout":{"type":"constrained"}} -->
    <div class="wp-block-group thread-header">
        <!-- wp:group {"className":"xhheader-conten","layout":{"type":"constrained"}} -->
        <div class="wp-block-group xhheader-conten">

            <!-- wp:post-title {"textAlign":"center","className":"thread-title"} /-->

            <!-- wp:group {"className":"thread-meta-wrapper","style":{"spacing":{"margin":{"top":"24px"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
            <div class="wp-block-group thread-meta-wrapper" style="margin-top:24px">
                <!-- wp:group {"className":"thread-stats","layout":{"type":"flex","flexWrap":"nowrap"}} -->
                <div class="wp-block-group thread-stats">
                    <!-- wp:html -->
                    <a href="#comments" class="xhbtn-discussion">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            style="margin-right:6px">
                            <path
                                d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                            </path>
                        </svg>
                        <?php esc_html_e('Join Topic Discussion', 'xhtheme-ai-toolbox'); ?>
                    </a>
                    <!-- /wp:html -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->

        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->

    <!-- wp:group {"className":"xh-content-section","style":{"spacing":{"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained","contentSize":"800px"}} -->
    <div class="wp-block-group xh-content-section" style="margin-top:0;margin-bottom:0">
        <!-- wp:group {"className":"xhprose","layout":{"type":"default"}} -->
        <div class="wp-block-group xhprose">
            <!-- wp:post-content /-->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->

    <!-- wp:group {"className":"comments-section","style":{"spacing":{"margin":{"top":"60px","bottom":"60px"}}},"layout":{"type":"constrained","contentSize":"800px"}} -->
    <div class="wp-block-group comments-section" style="margin-top:60px;margin-bottom:60px">
        <!-- wp:comments {"className":"xhaitool-comments"} -->
        <div class="wp-block-comments xhaitool-comments">
            <!-- wp:comments-title {"level":3} /-->

            <!-- wp:comment-template -->
            <!-- wp:columns -->
            <div class="wp-block-columns">
                <!-- wp:column {"width":"40px"} -->
                <div class="wp-block-column" style="flex-basis:40px">
                    <!-- wp:avatar {"size":40,"style":{"border":{"radius":"50%"}}} /-->
                </div>
                <!-- /wp:column -->

                <!-- wp:column -->
                <div class="wp-block-column">
                    <!-- wp:comment-author-name {"fontSize":"small"} /-->

                    <!-- wp:group {"style":{"spacing":{"margin":{"top":"0.5em","bottom":"0.5em"}}},"layout":{"type":"flex"}} -->
                    <div class="wp-block-group" style="margin-top:0.5em;margin-bottom:0.5em">
                        <!-- wp:comment-date {"fontSize":"small"} /-->
                        <!-- wp:comment-edit-link {"fontSize":"small"} /-->
                    </div>
                    <!-- /wp:group -->

                    <!-- wp:comment-content /-->

                    <!-- wp:comment-reply-link {"fontSize":"small"} /-->
                </div>
                <!-- /wp:column -->
            </div>
            <!-- /wp:columns -->
            <!-- /wp:comment-template -->

            <!-- wp:comments-pagination -->
            <!-- wp:comments-pagination-previous /-->
            <!-- wp:comments-pagination-numbers /-->
            <!-- wp:comments-pagination-next /-->
            <!-- /wp:comments-pagination -->

            <!-- wp:post-comments-form /-->
        </div>
        <!-- /wp:comments -->
    </div>
    <!-- /wp:group -->

</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->