var xmw_wechat_login =  new Vue({
    el: '#xmw_wechat_login',
    data: {
        code: '',
        show: false,
        button:false,
    },
    methods: {

        close() {
            this.show = !this.show;
            this.code = '';
            login.show = !this.show;
        },

        post_data(){
                if(!this.code){
                    Qmsg['warning']('验证码不能为空', {
                        html: true
                    });
                    return;
                } 
            
                let code = {
                    'code' : this.code,
                }
                this.button = true;
                this.$http.post(b2_global.rest_url + 'xmw/getWeChatLoginData', code).then(res =>{
                    this.button = false;
                    if(res.data.id){
                        location.reload();
                    }else if(res.data.msg){
                        xmw_wechat_bind.token = res.data.token
                        xmw_wechat_bind.close();
                    }

                }).
                catch(err =>{
                    this.button = false;
                    Qmsg['warning'](err.response.data.message, {
                        html: true
                    });
                });

        }

    }
});

var xmw_wechat_bind =  new Vue({
    el: '#xmw_wechat_bind',
    data: {
        code: '',
        show: false,
        token:'',
        remainingTime: 300,
        timer:'',
        
        username:'',
        password:'',
    },
    methods: {
        
        cancel(){
            this.code = '';
            this.token = '';
            clearInterval(this.timer);
        },

        close(e) {
            this.show = !this.show;
            
            if(e == 2){
                this.code = '';
                this.token = '';
                clearInterval(this.timer);
                
            }else{
                
                if(this.token){
                    this.remainingTime = 300;
                    this.countDown();
                }
            
                
            }
            
            xmw_wechat_login.show = !xmw_wechat_login.show;

        },
        
        yz_bind(){
            this.remainingTime = 300;
            if(!this.code){
                Qmsg['warning']('验证码不能为空', {
                    html: true
                });
                return;
            }
            
            let code = {
                'code' : this.code,
            }
            this.$http.post(b2_global.rest_url + 'xmw/getWeChatLoginData', code).then(res =>{

                if(res.data.id){
                    
                    location.reload();
                    
                }else if(res.data.token){
                    this.countDown();
                    this.token = res.data.token
                }

            }).
            catch(err =>{
                Qmsg['warning'](err.response.data.message, {
                    html: true
                });
            });
        
            
        },
        
        yzBindToken(e){
            
            if(!this.token){
                Qmsg['warning']('Token不能为空', {
                    html: true
                });
                return;
            }
            
            if(!e && (!this.username || !this.password)){
                 Qmsg['warning']('账号密码不能为空', {
                    html: true
                });
                return;
            }
            
            let data = {
                'type' : e,
                'token' : this.token,
                'username' : this.username,
                'password' : this.password,
            }
            this.$http.post(b2_global.rest_url + 'xmw/yzBindToken', data).then(res =>{

                if(res.data.msg){
                    Qmsg['warning'](res.data.msg, {
                    html: true
                    });
                    
                    location.reload();
                }

            }).
            catch(err =>{
                Qmsg['warning'](err.response.data.message, {
                    html: true
                });
            });
            
            
        },
        
    countDown() {
      // 每秒更新倒计时剩余时间
      this.timer = setInterval(() => {
        if (this.remainingTime > 1) {
          this.remainingTime--;
        } else {
          clearInterval(this.timer);
          this.code = '';
          this.token = '';
        }
      }, 1000);
    }


    }
});