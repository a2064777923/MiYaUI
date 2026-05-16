<div id="ask-archive" class="ask-list ji-ask-archive" ref="asklist">
    <div class="gujia" ref="gujia">
        <div id="jitheme_ask_list" class="ask-list-box rank-list">
        <?php
        for ($i = 0; $i < 12; $i++) {
            ?>
            <div class="ask-item talk-item box b2-radius">
        <div class="item-wrap ranks_item ">
            <div class="item-in b2flex ji-between">
                <div class="ji-ask-right">
                    <div class="item-title">
                        <div class="item-avatar"></div>
                        <div class="item-author">
                            <h2 class="author-name">
                                <span></span>
                            </h2>
                        </div>
                    </div>
                    <div class="ask-item-top b2flex"></div>
                    <div class="item-entry"><a class="entry">
                            <h2 class="talk-title"></h2>
                            <div class="ask-desc"></div>
                        </a></div>
                </div>
            </div>
        <div class="rank-ask-img-box">
            <div class="images_imagesWrap b2-radius"></div>
        </div>
            <div class="circle_bottom">
                <div class="bottomLeft___2cVLU"><span class="ask-aks-date"></span></div>
                <div class="bottomRight___YP91A">
                    <div class="ask-footer"><span></span> <span></span> <span></span></div>
                </div>
            </div>
        </div>
    </div>
            <?php
        }
        ?>
        </div>
    </div>
    <div class="ask-bar b2flex">
        <div>
            <span :class="{'picked':opt.type == 'hot'}" @click="fliter('hot')"><?php echo __('热门','b2'); ?></span>
            <span :class="{'picked':opt.type == 'last'}" @click="fliter('last')"><?php echo __('最新','b2'); ?></span>
            <span :class="{'picked':opt.type == 'waiting'}" @click="fliter('waiting')"><?php echo __('等待回答','b2'); ?></span>
        </div>
        <div>
            <a href="<?php echo b2_get_custom_page_url('po-ask'); ?>" target="_blank" class="b2-color button empty b2flex"><?php echo jitheme_get_icon('Jifont-comme',null,'Jifont_pad').__('提问','b2'); ?></a>
        </div>
    </div>
    
    <div id="jitheme_ask_list" class="ask-list-box rank-list" v-if="data != ''" v-cloak>
        <template v-if="empty">
            <div style="width:100%;height:100%">
                <?php echo B2_EMPTY; ?>
            </div>
        </template>
        <div class="ask-item talk-item box  b2-radius" v-for="(item,i) in data.data" v-else>
            <div class="item-wrap ranks_item">
                <div class="b2flex ji-between">
                    <div class="ji-ask-right">
                        <div class="item-title">
                            <div class="item-avatar">
                                <img :src="item.author.avatar" alt="" class="lazy entered loaded" data-ll-status="loaded">
                            </div>
                            <div class="item-author">
                                <h2 class="author-name"><strong><a :href="item.author.link" target="_blank"><span v-text="item.author.name"></span></a></strong> 
                                    <span v-if="item.metas.inv.length > 0">
                                        <?php echo sprintf(__('邀请%s回答此问题','b2'),'<a class="red" :href="u.link" target="_blank" v-for="(u,ui) in item.metas.inv" :key="ui"><b v-text="u.name"></b></a>'); ?>
                                    </span>
                                    <span v-else><?php echo sprintf(__('邀请你来回答','b2')); ?></span>
                                </h2>
                            </div>
                            <div class="users-list">
                                
                    
                                <div class="users-list-answer">
                                    <li v-for="(answer_user, index) in item.metas.answer_user" 
                                        v-if="index < 5 && answer_user.user_avatar" 
                                        :key="answer_user.author_id" :class="answer_user.best === 1 ? 'best-style' : ''">
                                        <img :src="answer_user.user_avatar" :alt="answer_user.user_name">
                                    </li>
                                </div>
                                <span class="item-dateico item-date">共<span v-html="item.metas.answer_count"></span>个回答<?php echo jitheme_get_icon('Jifont-right') ?></span>
                            </div>
                        </div>
                        <div class="ask-item-top b2flex">
                <div class="ask-pay b2flex b2-radius" v-if="item.metas.reward">
                    <div class="ask-reward  b2-radius"><?php echo __('悬 赏','b2'); ?></div>
                    <div class="ask-reward-pay-left">
                        <div class="ask-pay-number b2flex">
                            <span v-if="item.metas.reward.rewardType == 'credit'">
                                <?php echo b2_get_icon('b2-coin-line'); ?>
                                <b v-text="item.metas.reward.money"></b>
                            </span>
                            <span v-else>
                                <?php echo B2_MONEY_SYMBOL; ?>
                                <b v-text="item.metas.reward.money"></b>
                            </span>
                        </div>
                        <div class="ask-pay-type">
                            <span v-if="!item.metas.endtime" class="ask-passtime"><?php echo __('悬赏已过期','b2'); ?></span>
                            <span class="ask-passtime" v-else><?php echo sprintf(__('%s后悬赏过期'),'<b v-text="item.metas.endtime"></b>'); ?></span>
                        </div>
                    </div>
                   
                </div>
            </div>
                        <div class="item-entry">
                            <a :title="item.title" :href="item.link" target="_blank" class="entry">
                                <h2 class="talk-title">
                                    <div class="last-answer" v-if="item.metas.best"><div class="answer-ok b2-radius"><?php echo jitheme_get_icon('Jifont-shield') ?>已解决</div></div>
                                    <span class="last-answer-title">{{item.title}}</span>
                                    <span v-if="item.data_new" v-html="item.data_new"></span>   
                                    <span v-if="item.metas.answer_count > 1 && item.metas.answer_count <= 3" class="ico icon-talk-hot-2"></span>
                                    <span v-if="item.metas.answer_count > 3 && item.metas.answer_count < 6" class="ico icon-talk-hot-3"></span>
                                    <span v-if="item.metas.answer_count > 6 && item.metas.answer_count < 10" class="ico icon-talk-hot-4"></span>
                                </h2>
                                <div class="ask-desc" v-if="item.desc" v-html="item.desc"></div>
                                <div class="rank-ask-img-box" v-if="item.image">
                                    <div class="images_imagesWrap b2-radius" v-for="(img,index) in item.image" v-if="index <= 4" >
                                        <img :src="img" />
                                        <div  v-if="index === 4 && item.image.length > 5" class="moreImgeTip">
                                           <span> +<span v-text="item.image.length - 5"></span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ask-answer-user-list" v-for="(answer_user, index) in item.metas.answer_user" v-if="answer_user.best === 1 ">
                                    
                                    <div class="common-desc">
                                        <span v-text="answer_user.user_name" class="answer-name"></span>
                                        <sapn>:</sapn> 
                                        <sapn class="ask-answer-user-con" v-html="answer_user.content"></sapn>
                                    </div>
                                </div>   
                            </a>
                        </div>
                    </div>
                    
                </div>
                
                <div class="circle_bottom">
                    <div class="bottomLeft___2cVLU">
                    <a v-for="(tag,index) in item.tags" v-if="index < 3" :style="'background:'+tag.bgcolor+';color:'+tag.color" class="circleWrap____hhZ0" :href="tag.link" target="_blank"><?php echo jitheme_get_icon('Jifont-info') ?>{{tag.name}}</a>
                    </div>
                    <div class="bottomRight___YP91A">
                        <div class="ask-footer">
                            <span><?php echo jitheme_get_icon('Jifont-time') ?><em v-html="item.metas.date"></em></span>
                            <span><?php echo jitheme_get_icon('Jifont-comm') ?>回答 <em v-text="item.metas.answer_count"></em></span> 
                            <span><?php echo jitheme_get_icon('Jifont-i-see') ?>阅读 <em v-text="item.metas.views"></em></span> 
                            <span><?php echo jitheme_get_icon('Jifont-collect-1') ?>收藏 <em v-text="item.metas.favorites"></em></span>
                        </div>
                    </div>
                </div>
                <div class="0"></div>
            </div>
        </div>
    </div>
</div>