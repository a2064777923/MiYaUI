<div id="ask-archive" class="ask-list" ref="asklist">
    <div class="gujia" ref="gujia">
        <div id="jitheme_ask_list" class="ask-list-box rank-list">
        <?php
        
        for ($i = 0; $i < 12; $i++) {
            ?>
<div class="ask-item talk-item">
        <div class="item-wrap ranks_item ">
            <div class="item-in b2flex ji-between">
                <div class="ji-ask-right">
                    <div class="item-title">
                        <div class="item-avatar"></div>
                        <div class="item-author">
                            <h2 class="author-name"><span>
                                    
                                </span></h2>
                        </div>
                    </div>
                    <div class="ask-item-top b2flex">
                    </div>
                    <div class="item-entry"><a class="entry">
                            <h2 class="talk-title">
                                
                            </h2>
                            <div class="ask-desc"></div>
                        </a></div>
                </div>
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
    <div id="jitheme_ask_list" class="ask-list-box rank-list" v-if="data != ''" v-cloak>
        <template v-if="empty">
            <div style="width:100%;height:100%">
                <?php echo B2_EMPTY; ?>
            </div>
        </template>
        <div class="ask-item talk-item box b2-radius" v-for="(item,i) in data.data" v-else>
            <div class="item-wrap ranks_item ">
                <div class="item-in b2flex ji-between">
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
                                <div class="last-answer" v-if="item.metas.best"><div class="answer-ok b2-radius"><i class="jitheme jitheme-thumb-up-line1"></i>已解决</div></div>
                                <span class="last-answer-title">{{item.title}}</span>
                                <span v-if="item.data_new" v-html="item.data_new"></span>   
                                <span v-if="item.metas.answer_count > 1 && item.metas.answer_count <= 3" class="ico icon-talk-hot-2"></span>
                                <span v-if="item.metas.answer_count > 3 && item.metas.answer_count < 6" class="ico icon-talk-hot-3"></span>
                                <span v-if="item.metas.answer_count > 6 && item.metas.answer_count < 10" class="ico icon-talk-hot-4"></span>
                            </h2>
                            <div class="ask-desc" v-if="item.desc" v-html="item.desc"></div>
                            </a>
                        </div>
                    </div>
                    
                </div>
                <div class="rank-ask-img-box" v-if="item.image">
                    <div class="images_imagesWrap b2-radius" v-for="(img,index) in item.image" v-if="index <= 4" >
                        <img :src="img" />
                        <div  v-if="index === 4 && item.image.length > 5" class="moreImgeTip">
                           <span> +<span v-text="item.image.length - 5"></span></span>
                        </div>
                    </div>
                </div>
                <div class="circle_bottom">
                                <div class="bottomLeft___2cVLU">
                                <a v-for="(tag,index) in item.tags" v-if="index < 3" :style="'background:'+tag.bgcolor+';color:'+tag.color" class="circleWrap____hhZ0" :href="tag.link" target="_blank"><i class="jitheme jitheme-integral"></i>{{tag.name}}</a>
                                </div>
                                <div class="bottomRight___YP91A">
                                    <div class="ask-footer">
                                        <span><i class="jitheme jitheme-time_fill"></i><b v-html="item.metas.date"></b></span>
                                        <span><i class="jitheme jitheme-message_fill"></i>回答 <b v-text="item.metas.answer_count"></b></span> 
                                        <span><i class="jitheme jitheme-browse_fill"></i>阅读 <b><b v-text="item.metas.views"></b></b></span> 
                                        <span><i class="jitheme jitheme-like_fill"></i>收藏 <b><b v-text="item.metas.favorites"></b></b></span>
                                    </div>
                                </div>
                            </div>
            </div>
        </div>
    </div>
</div>