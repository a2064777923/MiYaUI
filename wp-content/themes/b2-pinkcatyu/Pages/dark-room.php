<?php
/**
 * 小黑屋
 */
get_header();
?>
<style>
 .xypro_describe_content table tbody tr:nth-child(odd){
     background:#fff!important;
 }
 
    .xypro_describe_content img{
    width: 20px;
    height: 20px;
    border-radius: 100%;
    margin-right: 8px;
    margin-top: -3px;
    }
  .xypro_describe_content   thead>tr>th{
        background:rgba(0, 0, 0, 0.03);
        text-align: center;
        border: 1px solid rgba(50, 50, 50, 0.06);
    padding: 8px;
    line-height: 1.42857143;
    vertical-align: top;
    }
   .xypro_describe_content  .yq{
            color: #909399;
    }
    
   .xypro_describe_content  .yq_link td {
    text-align: center;
    
    }
    
</style>
<div class="b2-single-content wrapper">
    
   
    <div id="dark-room" class="content-area wrapper" ref="darkRoom">
        <main id="main" class="site-main b2-radius box entry-content" style="padding:10px">
            
            
            <div class="wp-block-zibllblock-quote">
                        
                           
                            
                            
                            <blockquote class="wp-block-quote is-layout-flow wp-block-quote-is-layout-flow">
<p  >本网站严禁发布违法违规以及发布毫无意义的评论等内容，如有违反，封禁处理，以下是本站封禁用户列表，请大家珍惜自己的账户！</p>
</blockquote>
                            
                            
                            
                        </div>
                        <div style="padding: 0 20px;margin-bottom: 20px;background:url(<?php echo B2_CHILD_URI.'/pic/darkroom.svg'; ?>);    background-size: 150% 130%;
    background-repeat: no-repeat;">
                            <div>
                            <p><h2 style="font-size:20px">为什么会被关进小黑屋？</h2></p>
                            

<p>1、故意频繁请求网站接口；</p>

<p>2、威胁网站安全；</p>

<p>3、违反国家相关法律和道德要求；</p>

<p>4、谩骂他人以及对他人进行言语攻击；</p>

<p>5、发布带有广告性质的内容。</p>
                            
                            </div>
                        </div>
            <div class="dark-room-bar box" style="font-size:15px">
                <div class="dark-bar-l">
                    <template v-if="!type">
                        <?php echo sprintf(__('封禁列表（%s名成员）','b2'),'<b>{{data.count}}','</b>'); ?>
                    </template>
                    <template v-else-if="type == 'ls'">
                        <?php echo sprintf(__('临时禁闭（%s名成员）','b2'),'<b>{{data.count}}','</b>'); ?>
                    </template>
                    <template v-else>
                        <?php echo sprintf(__('永久禁闭（%s名成员）','b2'),'<b>{{data.count}}','</b>'); ?>
                    </template>
                </div>
                <div class="dark-bar-r">
                    <a href="javascript:void(0)" @click="getDarkRoomUsers()" :class="type === undefined ? 'b2-color' : ''"><?php echo __('所有禁闭','b2'); ?></a>
                    <a href="javascript:void(0)" @click="getDarkRoomUsers('ls')" :class="type === 'ls' ? 'b2-color' : ''"><?php echo __('临时禁闭','b2'); ?></a>
                    <a href="javascript:void(0)" @click="getDarkRoomUsers('yy')" :class="type === 'yy' ? 'b2-color' : ''"><?php echo __('永久禁闭','b2'); ?></a>
                </div>
            </div>
            <div class="dark-room-list">
                <div class="button empty b2-loading empty-page text" v-if="data == ''"></div>
                
                
                
                 <div id="xy_hide" class="xypro_describe_content xy_height_hide"  v-else-if="data.data.length > 0" v-cloak>
                    <table class="yq" >
                        <thead>
                            <tr>
                                <th align="center" width="5%"><strong>用户名</strong></th>
                                <th align="center" width="5%"><strong>封禁开始时间</strong></th>
                                <th align="center" width="5%"><strong>封禁类型</strong></th>
                                <th align="center" width="5%"><strong>封禁时长</strong></th>
                                <th align="center" width="5%"><strong>封禁原因</strong></th>
                                
                            </tr>
                        </thead>
                        
                        
                        
                        <tr class="yq_link" v-for="item in data.data">
                    <td> <img :src="item.user.avatar" class="avatar" /><a :href="item.user.link" v-text="item.user.name" class="b2-color" target="_blank"></a></td>
                    <td v-html="item.start_date"></td>
                    <td>小黑屋</td>
                    <td><template v-if="item.days == 0">
                                    <?php echo __('永久禁闭，不会释放！','b2'); ?>
                                </template>
                                <template v-else>
                                    <?php echo sprintf(__('将在 %s 释放！','b2'),'<span v-html="item.end_date"></span>'); ?>
                                </template></td>
                    <td>{{item.why}}</td>
                    
                </tr>
                
                
                
                </tbody></table>
                
                </div>
                
                
                 <div v-else v-cloak>
                    <?php echo B2_EMPTY;?>
                </div>
                
               
                
               
                <div class="b2-pd" v-if="pages > 1" v-cloak>
                    <pagenav-new type="p" :paged="paged" :pages="pages" :opt="opt" api="getDarkRoomUsers" @return="getMoreRoomUsers"></pagenav-new>
                </div>
                 
            </div>
        </main>
    </div>
   
</div>
<?php
get_footer();