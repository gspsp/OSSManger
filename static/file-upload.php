<?php if (!defined('__TYPECHO_ADMIN__')) exit; ?>

<?php
if (isset($post) || isset($page)) {
  $cid = isset($post) ? $post->cid : $page->cid;

  if ($cid) {
    Typecho_Widget::widget('Widget_Contents_Attachment_Related', 'parentId=' . $cid)->to($attachment);
?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/gspsp/bin/self/self.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/5.15.3/css/fontawesome.min.css" />
    <div id="app">
      <hr :style="'margin: 5px 0;width: '+process+'%;'">
      <button @click="Reverse()" type="button">反选</button>
      <button @click="Delete()" type="button">删除</button>
      <input @change="Upload($event)" type="file" />
      <table border="1" style="width: 100%;margin-top: 3px;">
        <thead>
          <tr>
            <th><input @click="Checkall()" v-model="allcheck" type="checkbox" /></th>
            <th>文件名</th>
            <th>文件大小</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(file,key) in files" v-show="file.name!=''">
            <th><input @change="Check()" v-model="file.checked" type="checkbox" /></th>
            <th>
              <span :class="'oss-rc-icon-file file-'+file.type" style="margin: 1px;"></span>
              {{file.name | name}}
            </th>
            <th>{{file.size | size}}</th>
            <th>
              <i @click="Copy(file,$event)" class="fa fa-clipboard fa-lg"></i>
              <i @click="Remove(key)" class="fa fa-remove fa-lg"></i>
            </th>
          </tr>
        </tbody>
      </table>
    </div>

    <script src="https://cdn.jsdelivr.net/gh/gspsp/bin/vue/dist/vue.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="https://cdn.jsdelivr.net/gh/gspsp/bin/vue-clipboard2/dist/vue-clipboard.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="https://cdn.jsdelivr.net/gh/gspsp/bin/ali-oss/dist/aliyun-oss-sdk.min.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript">
      let vue = new Vue({
        el: '#app',
        data: {
          files: [],
          config: OSSConfig,
          allcheck: false,
          prefix: 'archives/<?php echo $cid; ?>/',
          process: 100
        },
        methods: {
          Upload(f) {
            this.OSS.multipartUpload(this.prefix + f.target.files[0].name, f.target.files[0], {
              progress: (p, cpt, res) => {
                this.process = p * 100
              }
            }).then((e) => {
              this.Refresh()
            });
          },
          Delete() {
            for (let i = 0; i < this.files.length; i++) {
              if (this.files[i].checked) {
                this.Remove(i)
                i--
              }
            }
            this.Check()
          },
          Check() {
            this.allcheck = true
            this.files.forEach((cur) => {
              this.allcheck = cur.checked && this.allcheck
            })
            if (this.files.length == 0) this.allcheck = false
          },
          Checkall() {
            this.allcheck = !this.allcheck
            this.files.forEach((cur) => {
              cur.checked = this.allcheck
            })
          },
          Reverse() {
            this.allcheck = true
            this.files.forEach((cur) => {
              cur.checked = !cur.checked
              this.allcheck = cur.checked && this.allcheck
            })
          },
          Copy(f, e) {
            this.$copyText(this.config.selfDomain != '' ? this.config.selfDomain + f.path : f.url)
            e.target.className = 'fa fa-check fa-lg'
            setTimeout(() => {
              e.target.className = 'fa fa-clipboard fa-lg'
            }, 200)
          },
          Remove(k) {
            this.OSS.delete(this.files[k].path).then(this.files.splice(k, 1));
          },
          Refresh() {
            this.OSS.listV2({
              prefix: this.prefix
            }).then((r) => {
              this.files = []
              if (r.objects) {
                r.objects.forEach(cur => {
                  this.files.push({
                    name: cur.name.split('').reverse().join('').split('/')[0].split('').reverse().join(''),
                    url: cur.url,
                    type: cur.name.split('').reverse().join('').split('.')[0].split('').reverse().join(''),
                    size: cur.size,
                    checked: false,
                    path: cur.name
                  })
                })
              }
            })
          }
        },
        created() {
          this.OSS = new OSS(this.config)
          this.Refresh()
        },
        filters: {
          size(n) {
            p = ['B', 'kB', 'mB', 'gB', 'tB']
            p.reverse()
            while (n > 1024)(n /= 1024) && p.pop()
            p.reverse()
            return n.toFixed(2) + p[0]
          },
          name(n) {
            if (n.length > 16) n = n.substr(0, 8) + '...' + n.substr(n.length - 8, 8)
            return n
          }
        }
      })
    </script>
<?php
  } else {
    Typecho_Widget::widget('Widget_Contents_Attachment_Unattached')->to($attachment);
  }
}
?>
