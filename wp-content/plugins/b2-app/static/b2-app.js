const b2AppUpgrade = new Vue({
    el:'#b2-app-upgrade-box',
    data:{
        data:null,
        loading:false,
		count:0,
		timer:null
    },
    methods:{
        checkUpdate(){
            this.loading = 'waiting';
            that = this
            jQuery.ajax({
				url: '/wp-admin/admin-ajax.php',
				type: 'POST',
				data: {
					action: 'b2_app_version'
				},
				dataType: 'json',
				success: function (res) {
					console.log(res)
					const data = res.data

					if (res.success) {
						that.loading = ''
						that.data = data
					} else {
						alert(data)
						that.loading = ''
					}
				}
			}).fail(function (jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseJSON)

				//弹出错误
				alert(jqXHR.responseJSON.message)
				that.loading = ''
			})
        },
        upgrade(){
			const that = this
			that.loading = 'waiting'
			console.log(this.count)
			if(this.count > 10){
				clearTimeout(that.timer);
				this.count = 0;
				that.loading = ''
				alert('升级失败，请稍后再试');
				return 
			}
			jQuery.ajax({
				url: '/wp-admin/admin-ajax.php',
				type: 'POST',
				data: {
					action: 'b2_app_upgrade',
				},
				dataType: 'json',
				success: function (res) {
					console.log(res)
					if (res.data == 'waiting') {
						clearTimeout(that.timer)
						this.timer = setTimeout(() => {
							that.count++
							that.upgrade()
						}, 5000);

					} else if(res.data == 'success') {
						alert('升级成功')
						that.loading = ''
						that.count = 0
						location.reload();
					} else{
						clearTimeout(that.timer)
						alert(res.data)
						that.loading = ''
						that.count = 0
					}
				}
			}).fail(function (jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseJSON)
				that.count = 0
				//弹出错误
				alert(jqXHR.responseJSON.message)
				that.loading = ''
			})
        }
    }
});