/**
 * 统一表格
 */
Vue.component('el-curd', {
    template: `
    <div class="el-curd">
        <slot name="warp"></slot>
        <el-form 
            ref="search" 
            class="el-curd-header" 
            size="small" 
            :inline="true" 
            :model="search" 
            @submit.native.prevent>
            <el-form-item class="el-button-form">
                <el-button 
                    v-if="searchRefresh"
                    type="info" 
                    icon="el-icon-refresh" 
                    @click="refreshData()">
                    刷新
                </el-button>
                <el-button 
                    v-if="tableTree" :icon="expand ? 'el-icon-arrow-down' : 'el-icon-arrow-right'" 
                    @click="expandAll()">
                    {{expand ? '折叠' : '伸展'}}
                </el-button>
                <el-button 
                    v-if="deleteAuthority" 
                    :disabled="rows.length === 0"
                    type="danger" 
                    icon="el-icon-delete" 
                    @click="removeData()">
                    删除
                </el-button>
                <el-button 
                    v-if="saveAuthority" 
                    type="primary" 
                    icon="el-icon-plus" 
                    @click="openData()">
                    添加
                </el-button>
                <el-dropdown v-if="tableExport" @command="exportData">
                    <el-button type="success" icon="el-icon-download">导出</el-button>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item command="csv">CSV</el-dropdown-item>
                        <el-dropdown-item command="json">JSON</el-dropdown-item>
                        <el-dropdown-item command="xhtml">XHTML</el-dropdown-item>
                        <el-dropdown-item command="txt">TXT</el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
                <slot name="button"></slot>
            </el-form-item>
            <el-form-item prop="date" v-if="searchDate">
                <el-date-picker 
                    v-model="search.date" 
                    type="daterange" 
                    align="right" 
                    unlink-panels 
                    range-separator="至" 
                    start-placeholder="开始日期" 
                    end-placeholder="结束日期" 
                    format="yyyy-MM-dd" 
                    value-format="yyyy-MM-dd" 
                    :picker-options="picker" 
                    @change="searchData()">
                </el-date-picker>
            </el-form-item>
            <el-form-item prop="keyword" v-if="searchKeyword">
                <el-input 
                    :placeholder="searchKeyword === true ? '根据关键词搜索' : searchKeyword" 
                    v-model="search.keyword" 
                    @keyup.enter.native="searchData()">
                    <el-button slot="append" icon="el-icon-search" @click="searchData()"></el-button>
                </el-input>
            </el-form-item>
            <el-form-item prop="catalog" v-if="searchCatalog.length > 0">
                <el-select v-model="search.catalog" placeholder="查看所有分类" @change="searchData()" filterable>
                    <el-option label="全部分类" value=""></el-option>
                    <el-option v-for="(item, index) in searchCatalog" :key="index" :label="item.title" :value="item.id">
                        <span v-html="item.treeString"></span>{{ item.title }}
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item prop="status" v-if="searchStatus.length > 0">
                <el-select v-model="search.status" placeholder="查看所有状态" @change="searchData()">
                    <el-option label="全部状态" value=""></el-option>
                    <el-option v-for="(item, index) in searchStatus" :key="index" :label="item.label" :value="item.value"></el-option>
                </el-select>
            </el-form-item>
            <slot name="search"></slot>
        </el-form>
        <el-table 
            ref="table"
            v-loading="loading"
            :row-key="rowKey"
            :class="{'el-tree-table': tableTree}"
            :data="table"
            :tree-props="{children: 'children', hasChildren: 'hasChildren'}"
            :default-expand-all="tableExpand"
            :highlight-current-row="true"
            :row-class-name="tableRowClass"
            @select="selectRow"
            @select-all="selectAll"
            @selection-change="selectionChange"
            @sort-change="sortChange">
            <el-table-column v-if="tableSelection" type="selection" width="55" :selectable="selectableDisabled"></el-table-column>
            <template v-for="(item, index) in column">
                <el-table-column
                    :key="index"
                    :prop="item.prop" 
                    :width="item.table.width" 
                    :label="typeof item.table.label !== 'undefined' ? item.table.label : item.label" 
                    :type="typeof item.table.expand !== 'undefined' && item.table.expand ? 'expand' : undefined"
                    :sortable="typeof item.table.sort !== 'undefined' && item.table.sort ? 'custom' : false">
                    <template slot-scope="scope">
                        <el-image 
                            v-if="item.table.is === 'image'"
                            :src="scope.row[item.prop]" 
                            :preview-src-list="[scope.row[item.prop]]">
                            <div slot="error" class="image-slot">
                                <img class="error-image" src="/admin/images/error.png"/>
                            </div>
                        </el-image>
                        <template 
                            v-if="item.table.is === 'object' && scope.row[item.prop] != null">
                            <div 
                                v-for="(child, index) in item.table.child" 
                                :key="index" 
                                v-html="typeof item.table.prop === 'undefined' ? scope.row[item.prop][child] : scope.row[item.table.prop][child]">
                            </div>
                        </template>
                        <span 
                            v-if="item.table.is === 'text'">
                            {{typeof item.table.prop === 'undefined' ? scope.row[item.prop] : scope.row[item.table.prop]}}
                            </span>
                        <el-switch
                            v-if="item.table.is === 'el-switch'"
                            v-model="scope.row[item.prop]"
                            :active-value="1"
                            :inactive-value="0"
                            :disabled="scope.row.disabled"
                            @change="oneKeyData(scope.row)">
                        </el-switch>
                        <el-input 
                            v-if="item.table.is === 'el-input'"
                            class="el-curd-table-input" 
                            v-model="scope.row[item.prop]" 
                            size="small" 
                            :disabled="scope.row.disabled" 
                            @change="oneKeyData(scope.row)">
                        </el-input>
                        <span 
                            v-if="typeof item.table.is === 'undefined'" 
                            v-html="typeof item.table.prop === 'undefined' ? scope.row[item.prop] : scope.row[item.table.prop]">
                        </span>
                        <template v-if="typeof item.table.bind !== 'undefined'">
                            <div v-for="(bind, index) in item.table.bind" :key="index" v-html="scope.row[bind]"></div>
                        </template>
                    </template>
                </el-table-column>
            </template>
            <el-table-column v-if="operationWidth > 0" label="操作" :width="operationWidth">
                <template slot-scope="scope">
                    <template v-if="scope.row.disabled !== true">
                        <el-tooltip content="拖动排序" placement="top" v-if="dropAuthority">
                            <el-button size="mini" class="rank" type="info" icon="el-icon-rank" circle></el-button>
                        </el-tooltip>
                        <el-tooltip content="复制" placement="top" v-if="saveAuthority && tableOperationCopy">
                            <el-button type="primary" size="mini" icon="el-icon-copy-document" circle @click="copyData(scope.row)"></el-button>
                        </el-tooltip>
                        <el-tooltip content="追加" placement="top" v-if="pushAuthority">
                            <el-button type="primary" size="mini" icon="el-icon-plus" circle @click="pushData(scope.row)"></el-button>
                        </el-tooltip>
                        <el-tooltip content="编辑" placement="top" v-if="updateAuthority && scope.row.update_disabled !== true">
                            <el-button type="warning" size="mini" icon="el-icon-edit" circle @click="openData(scope.row)"></el-button>
                        </el-tooltip>
                        <el-tooltip content="删除" placement="top" v-if="deleteAuthority && scope.row.delete_disabled !== true">
                            <el-button type="danger" size="mini" icon="el-icon-delete" circle @click="removeData(scope.row)"></el-button>
                        </el-tooltip>
                        <el-tooltip content="预览" placement="top" v-if="preview">
                            <a style="margin:0 10px;display:inline-block" :href="scope.row.url" target="_blank">
                                <el-button type="info" size="mini" icon="el-icon-search" circle></el-button>
                            </a>
                        </el-tooltip>
                        <slot name="operation" v-bind="scope.row"></slot>
                    </template>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
            v-if="! tableTree"
            layout="total, sizes, prev, pager, next, jumper"
            :current-page="search.page"
            :page-size="search.pageSize"
            :page-sizes="pageSizes"
            :total="total"
            :hide-on-single-page="true"
            background
            @size-change="pageSizeChange"
            @current-change="pageChange">
        </el-pagination>
        <el-drawer :visible.sync="drawer" :with-header="false" size="100%">
            <el-page-header @back="drawer=false" :content="drawerData[rowKey] === '' ? '添加' : '编辑'">
                <template v-slot:title>Esc键返回</template>
            </el-page-header>
            <el-form 
                ref="drawerData" 
                class="el-layout" 
                :class="{'el-layout-one': colrow.length === 1}" 
                :rules="drawerRules" 
                :model="drawerData" 
                :label-width="formLabelWidth" 
                :validate-on-rule-change="false" 
                @submit.native.prevent>
                <el-tabs :tab-position="document.body.clientWidth > 768 ? 'left' : 'top'">
                    <el-tab-pane v-for="(tab, key) in colrow" :name="String(key)" :index="key">
                        <span slot="label">
                            <i :class="tab.icon" v-if="tab.icon"></i>{{tab.label}}
                        </span>
                        <div :class="tab.warp === false ? '' : 'el-pane-warp'">
                            <el-row :gutter="formGutter">
                                <template v-for="(item, index) in tab.field" :key="index">
                                    <el-col 
                                        v-if="formItemShow(item)" 
                                        :md="typeof item.form.colMd == 'undefined' ? formColMd : item.form.colMd" 
                                        :xs="typeof item.form.colSm == 'undefined' ? formColSm : item.form.colSm">
                                        <el-form-item 
                                            :prop="item.prop"
                                            :label-width="item.label == '' ? '0px' : ''">
                                            <template v-slot:label>
                                                <el-tooltip placement="top" :content="formVariable(item)" :disabled="variable == ''">
                                                    <i>{{item.label == '' ? '' : item.label + '：'}}</i>
                                                </el-tooltip>
                                            </template>
                                            <component 
                                                class="el-component"
                                                v-model="drawerData[item.prop]"
                                                :is="item.form.is"
                                                :key="drawerData[rowKey] + index + randId"
                                                :type="item.form.type"
                                                :style="item.form.style"
                                                :editorcss="item.form.editorcss"
                                                :options="item.form.options"
                                                :disabled="item.form.disabled"
                                                :placeholder="item.form.placeholder"
                                                :filterable="item.form.filterable"
                                                :multiple="item.form.multiple"
                                                :remote="typeof item.form.remote !== 'undefined'"
                                                :remote-method="typeof item.form.remote !== 'undefined' ? (query)=>{remoteMethod(query, item.form)} : undefined"
                                                :props="item.form.props"
                                                :label-position="item.form.labelPosition"
                                                :label-width="formLabelWidth"
                                                :format="item.form.format"
                                                :value-format="item.form.valueFormat"
                                                :maxlength="item.form.maxlength"
                                                :minlength="item.form.minlength"
                                                :active-value="typeof item.form.activeValue === 'undefined' ? 1 : item.form.activeValue"
                                                :inactive-value="typeof item.form.inactiveValue === 'undefined' ? 0 : item.form.inactiveValue"
                                                :data="typeof item.form.data  === 'undefined' ? undefined : drawerData"
                                                :is-range="item.form.type == 'is-range'"
                                                :search="item.form.search"
                                                :ifset="item.form.ifset"
                                                :list="item.form.list"
                                                arrow-control
                                                show-word-limit
                                                @input="formRules()">
                                                <template v-if="typeof item.form.child !== 'undefined'">
                                                    <template v-if="item.form.is == 'el-radio-group' || item.form.is == 'el-checkbox-group'">
                                                        <component 
                                                            v-for="(val, key) in formChildValue(item)"
                                                            :is="item.form.is == 'el-radio-group' ? 'el-radio' : 'el-checkbox'"
                                                            :key="val.label"
                                                            :label="typeof val.value == 'undefined' ? val.label : val.value">
                                                            {{ val.label }}
                                                        </component>
                                                    </template>
                                                    <template v-else>
                                                        <component 
                                                            v-for="(val, key) in formChildValue(item)"
                                                            is="el-option"
                                                            :key="val.value"
                                                            :label="val.label"
                                                            :value="typeof val.value == 'undefined' ? val.label : val.value">
                                                            <span v-html="val.treeString"></span>
                                                            {{ val.label }}
                                                        </component>
                                                    </template>
                                                </template>
                                            </component>
                                            <div class="el-tips" v-if="typeof item.form.tips !== 'undefined'" v-html="item.form.tips"></div>
                                        </el-form-item>
                                    </el-col>
                                </template>
                            </el-row>
                            <div v-if="tab.warp !== false" class="el-bottom">
                                <el-button size="medium" type="primary" icon="el-icon-refresh-right" @click="saveData()" :loading="drawerLoading">保 存</el-button>
                                <el-button size="medium" @click="drawer = false">返 回</el-button>
                            </div>
                        </div>
                    </el-tab-pane>
                    <slot name="form"></slot>
                </el-tabs>
            </el-form>
        </el-drawer>
    </div>
    `,
    props: {
        field: {
            type: Array,
            required: true
        },
        indexUrl: {
            type: String,
            default: controller + '/index',
        },
        saveUrl: {
            type: String,
            default: controller + '/save',
        },
        updateUrl: {
            type: String,
            default: controller + '/update',
        },
        deleteUrl: {
            type: String,
            default: controller + '/delete',
        },
        dropUrl: {
            type: String,
            default: controller + '/drop',
        },
        tableTree: {
            type: Boolean,
            default: false
        },
        tableExport:{
            type: Boolean,
            default: true,
        },
        tableSort: {
            type: Object,
            default: {},
        },
        tableExpand: {
            type: Boolean,
            default: false,
        },
        tableOperationWidth: {
            type: String,
            default: '',
        },
        tableOperationCopy: {
            type: Boolean,
            default: false,
        },
        tablePageSize: {
            type: Number,
            default: 20,
        },
        tablePageSizes: {
            type: Array,
            default: [20, 50, 100, 200, 500],
        },
        tableEmpty: {
            type: String,
            default: '暂无数据',
        },
        tableSelection: {
            type: Boolean,
            default: true
        },
        searchCatalog: {
            type: Array,
            default: [],
        },
        searchStatus: {
            type: Array,
            default: [],
        },
        searchKeyword: {
            type: String,
            default: true
        },
        searchRefresh: {
            type: Boolean,
            default: true
        },
        searchDate: {
            type: Boolean,
            default: true
        },
        formLabelWidth: {
            type: String,
            default: '120px'
        },
        formGutter: {
            type: Number,
            default: 10,
        },
        formColMd: {
            type: Number,
            default: 24,
        },
        formColSm: {
            type: Number,
            default: 24,
        },
        variable: {
            type: String,
            default: ''
        },
        preview: {
            type: Boolean,
            default: false,
        },
        rowKey: {
            type: String,
            default: 'id'
        },
    },
    data() {
        return {
            self: this,
            randId: common.id(16),
            catalogUrl: "catalog/query",
            column: [],
            colrow: [],
            rows: [],
            list: [],
            table: [],
            search: {
                keyword: '',
                date: '',
                catalog: '',
                status: '',
                prop: '',
                order: '',
                page: 1,
                pageSize: this.tablePageSize,
                theme: theme,
                param: locationUrl.get('param'),
            },
            pageSizes: this.tablePageSizes,
            expand: this.tableExpand,
            total: 0,
            loading: false,
            select: false,
            drawer: false,
            drawerData: {},
            drawerForm: {},
            drawerRules: {},
            drawerLoading: false,
            picker: {
                shortcuts: [{
                    text: '最近一周',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        end.setTime(start.getTime() + 3600 * 1000 * 24 * 1);
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                        picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近一个月',
                    onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        end.setTime(start.getTime() + 3600 * 1000 * 24 * 1);
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                        picker.$emit('pick', [start, end]);
                    }
                }]
            },
        }
    },
    mounted() {
        // 拖动排序
        let self = this;
        self.sortable = Sortable.create(self.$refs.table.$el.querySelectorAll('.el-table__body-wrapper > table > tbody')[0],{
            animation: 300,
            forceFallback: true,
            delay: 20,
            handle: '.rank',
            onEnd: evt => {
                if (evt.oldIndex !== evt.newIndex) {
                    let currRow = self.table.splice(evt.oldIndex, 1)[0];
                    self.table.splice(evt.newIndex, 0, currRow);
                    request.post(self.dropUrl, {table: self.table}, function(res){
                        self.getData();
                        self.$notify({ showClose: true, message: res.message, type: res.status});
                    });
                }
            }
        })
    },
    created() {
        let self = this;
        self.search.prop  = typeof self.tableSort.prop === 'undefined' ? '' : self.tableSort.prop,
        self.search.order = typeof self.tableSort.order === 'undefined' ? '' : self.tableSort.order
        self.colrow = typeof self.field[0]['field'] === 'undefined' ? [{label: '基础信息', field: self.field}] : self.field;
        self.colrow.forEach(function (items, index) {
            items.field.forEach(function (item, index) {
                // 表单
                item.form = typeof item.form === 'undefined' ? {} : item.form;
                item.form.is = typeof item.form.is === 'undefined' ? 'el-input' : item.form.is;
                // 默认值
                self.drawerForm[item.prop] = typeof item.form.default === 'undefined' ? '' : item.form.default;
                // 表格
                item.table = typeof item.table === 'undefined' ? {} : item.table;
                if (item.table) {
                    self.column.push(item);
                }
            })
        })
        self.getData();
        self.formRules();
    },
    computed: {
        saveAuthority() {
            return authority.indexOf(this.saveUrl) !== -1;
        },
        updateAuthority() {
            return authority.indexOf(this.updateUrl) !== -1;
        },
        deleteAuthority() {
            return authority.indexOf(this.deleteUrl) !== -1;
        },
        pushAuthority() {
            return this.saveAuthority && this.tableTree;
        },
        dropAuthority() {
            return authority.indexOf(this.dropUrl) !== -1;
        },
        operationWidth() {
            if (this.tableOperationWidth === '') {
                let width = 0;
                if (this.updateAuthority) width += 50;
                if (this.deleteAuthority) width += 50;
                if (this.dropAuthority)   width += 50;
                if (this.saveAuthority)   width += 50;
                if (this.pushAuthority)   width += 50;
                if (this.preview)         width += 50;
                return width;
            } else {
                return this.tableOperationWidth;
            }
        },
    },
    methods: {
        /**
         * 获取数据
         */
        getData() {
            let self = this;
            self.loading = true;
            self.search.order = self.search.order === 'ascending' || self.search.order === 'asc' ? 'asc' : 'desc';
            request.post(self.indexUrl, self.search, function(res) {
                if (res.status === 'success') {
                    self.list = res.data;
                    self.table = self.tableTree && self.search.keyword === '' ? tree.convert(self.list) : self.list;
                    self.total = res.count;
                    if (self.search.keyword === '') {
                        self.$emit('get-data', res);
                    }
                } else {
                    self.$notify({ showClose: true, message: res.message, type: res.status});
                }
                self.loading = false;
            });
        },
        /**
         * 查看数据
         */
        openData(row = "") {
            this.drawerData = row === "" ? JSON.parse(JSON.stringify(this.drawerForm)) : JSON.parse(JSON.stringify(row));
            this.drawerData.theme = theme;
            this.drawer = true;
            this.$emit('open-data', this.drawerData);
        },
        /**
         * 快捷修改数据
         */
        oneKeyData(row) {
            let self = this;
            request.post(self.updateUrl, row, function(res){
                if (res.status === 'success') {
                    self.$emit('save-data', res);
                } else {
                    self.$notify({ showClose: true, message: res.message, type: res.status});
                }
            });
        },
        /**
         * 保存数据
         */
        saveData() {
            let self = this;
            self.$refs.drawerData.validate((valid) => {
                if (valid) {
                    self.drawerLoading = true;
                    request.post(self.drawerData[self.rowKey] === '' ? self.saveUrl : self.updateUrl, self.drawerData, function(res){
                        self.drawerLoading = false;
                        if (res.status === 'success') {
                            self.getData();
                            self.drawer = false;
                            self.$emit('save-data', res);
                        }
                        self.$notify({ showClose: true, message: res.message, type: res.status});
                    });
                } else {
                    return false;
                }
            });
        },
        /**
         * 导出数据
         */
        exportData(type) {
            let self = this;
            let list = self.tableTree ? tree.convertString(self.list) : self.list;
            let str  = '';
            if (type === 'json') {
                str = JSON.stringify(list);
            } else {
                let props = [];
                let labels = [];
                self.column.forEach(function (item, index) {
                    let prop = typeof item.table.prop !== 'undefined' ? item.table.prop : item.prop;
                    let label = typeof item.table.label !== 'undefined' && item.table.label !== '' ? item.table.label : item.label;
                    if (typeof item.table.bind !== 'undefined' && item.table.bind.length > 0) {
                        prop = [prop];
                        item.table.bind.forEach(function (b,i) {
                            prop.push(b);
                        })
                    }
                    if (typeof item.table.child !== 'undefined' && item.table.child.length > 0) {
                        let key = prop;
                        prop = [];
                        item.table.child.forEach(function (b,i) {
                            prop.push(key + '.' + b);
                        })
                    }
                    props.push(prop);
                    labels.push(label);
                });
                str = labels.toString() + '\n';
                let rows = [];
                list.forEach(function (item, index) {
                    props.forEach(function (prop, index) {
                        if ((typeof prop == 'object')) {
                            prop.forEach(function (b,i) {
                                let g = prop.length - 1 == i ? '' : '|';
                                let k = b.split('.');
                                let v = item[prop[i]];
                                str += v + g; 
                            });
                            str += '\t,'; 
                        } else {
                            str += item[prop] + '\t,'; 
                        }
                    })
                    str += '\n';
                });
            }
            let link = document.createElement("a");
            link.href = 'data:text/' + type + ';charset=utf-8,\ufeff' + encodeURIComponent(str);
            link.download =  "onekeyadmin-" + common.dateTime() + "." + type;
            link.click();
        },
        /**
         * 追加数据
         */
        pushData(row) {
            let arr = JSON.parse(JSON.stringify(this.drawerForm));
            arr.pid = row[this.rowKey];
            this.openData(arr);
        },
        /**
         * 复制数据
         */
        copyData(row) {
            let arr = JSON.parse(JSON.stringify(row));
            arr[this.rowKey] = "";
            this.openData(arr);
        },
        /**
         * 删除数据
         */
        removeData(row = "") {
            let self = this;
            let ids  = row === "" ? common.arrayColumn(self.rows, self.rowKey) : [row[self.rowKey]]; 
            self.$confirm('确定删除数据吗？', '', { confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning'}).then(() => {
                request.post(self.deleteUrl, {'ids': ids}, function(res){
                    if (res.status === 'success') {
                        self.getData();
                        self.$emit('remove-data', res);
                    }
                    self.$notify({ showClose: true, message: res.message, type: res.status});
                });
            }).catch(() => {});
        },
        /**
         * 刷新数据
         */
        refreshData() {
            this.$refs.search.resetFields();
            this.search = Object.assign({}, this.search, {page: 1});
        },
        /**
         * 搜索数据
         */
        searchData() {
            this.search = Object.assign({}, this.search, {page: 1});
        },
        /**
         * 排序改变
         */
        sortChange(val) {
            this.search = Object.assign({}, this.search, {page: 1 ,prop: val.prop , order: val.order});
        },
        /**
         * 分页个数改变时
         */
        pageSizeChange(val) {
            this.search = Object.assign({}, this.search, {page: 1 ,pageSize: val});
        },
        /**
         * 分页改变时
         */
        pageChange(val) {
            this.search = Object.assign({}, this.search, {page: val});
        },
        /**
         * 表格行类名
         */
        tableRowClass({row, rowIndex}) {
            return typeof row.table_row_class !== 'undefined' ? row.table_row_class : '';
        },
        /**
         * 表格行不可选中
         */
        selectableDisabled(row, index) {
            if (row.disabled === true || row.delete_disabled === true) {
                return false;
            } else {
                return true;
            }
        },
        /**
         * 表格选中行，树形结构需要这样处理
         */
        selectRow(selection, row) {
            let self = this;
            self.$nextTick(() => {
                let select = self.rows.indexOf(row) !== -1;
                if (typeof row.children !== 'undefined') {
                    row.children.forEach(function (item, index) {
                        self.$refs.table.toggleRowSelection(item, select);
                        if (typeof item.children !== 'undefined') {
                            self.selectRow([], item);
                        }
                    })
                }
            })
        },
        /**
         * 表格选中所有行，树形结构需要这样处理
         */
        selectAll() {
            let self = this;
            self.select = !self.select;
            self.list.forEach(function (item, index){
                if (item.disabled !== true && item.delete_disabled !== true) {
                    self.$refs.table.toggleRowSelection(item, self.select);
                }
            });
        },
        /**
         * 表格选中行
         */
        selectionChange(rows) {
            this.rows = rows;
        },
        /**
         * 表格折叠/展开行，只有树形结构需要这样处理
         */
        expandAll() {
            let self = this;
            self.expand = !self.expand;
            self.list.forEach(function (item, index){
                self.$refs.table.toggleRowExpansion(item, self.expand);
            });
        },
        /**
         * 表单验证
         */
        formRules() {
            let self  = this;
            let rules = {};
            self.colrow.forEach( function (items, index) {
                if (typeof items.field !== 'undefined') {
                    items.field.forEach(function (item, index) {
                        if (typeof item.form.rules !== 'undefined') {
                            let test = common.parseJson(common.stringifyJson(item.form.rules));
                            test.forEach(function (rule, index) {
                                // 添加时不能为空
                                if (typeof rule.saveRequired !== 'undefined' && self.drawerData[self.rowKey] === '') {
                                    test.push({required: true, message: rule.message, trigger: 'blur'});
                                }
                                // 更新时不能为空
                                if (typeof rule.updateRequired !== 'undefined' && self.drawerData[self.rowKey] !== '') {
                                    test.push({required: true, message: rule.message, trigger: 'blur'});
                                }
                                // 关联时不能为空
                                if (typeof rule.relationRequired !== 'undefined') {
                                    let relationRequired = false;
                                    rule.relationRequired.forEach(function (r,i) {
                                        relationRequired = self.drawerData[r.prop] == r.value;
                                    })
                                    if (relationRequired) {
                                        test.push({required: true, message: rule.message, trigger: 'blur'});
                                    }
                                }
                            });
                            rules[item.prop] = test;
                        }
                    })
                }
            });
            this.drawerRules = rules;
        },
        /**
         * 表单提示变量
         */
        formVariable(item) {
            let raw = item.form.is == 'el-editor' ? '|raw' : '';
            return '{$' + this.variable + '.' + item.prop + raw + '}';
        },
        /**
         * 表单内值(远程搜索)
         */
        remoteMethod(query, form) {
            if (query !== '') {
                let self = this;
                request.post(form.remote, {keyword: query}, function(res){
                    if (res.status === 'success') {
                        form.child.value = res.data;
                    } else {
                        self.$notify({ showClose: true, message: res.message, type: res.status});
                    }
                });
            } else {
                form.child.value = [];
            }
        },
        /**
         * 表单内值
         */
        formChildValue(item) {
            let self = this;
            if (typeof item.form.child !== 'undefined') {
                if (typeof item.form.child.value !== 'undefined') {
                    if (item.form.child.value === 'this') {
                        // 本身
                        let list = [];
                        self.list.forEach(function (option, index) {
                            let value = typeof option[self.rowKey] === 'undefined' ? '' : option[self.rowKey];
                            let label = typeof option['title'] === 'undefined' ? '' : option['title'];
                            let pid   = typeof option['pid'] === 'undefined' ? 0 : option['pid'];
                            list.push({id: value, pid: pid, label: label, value: value});
                        })
                        // 树形
                        list = tree.convertString(list);
                        list.unshift({label: '最父级', value: 0});
                        return list;
                    } else {
                        // 传递
                        let list = item.form.child.value;
                        if (typeof item.form.child.props !== 'undefined') {
                            list.forEach(function (option, index) {
                                // 指定
                                if (typeof item.form.child.props.label !== 'undefined') option.label = option[item.form.child.props.label];
                                if (typeof item.form.child.props.value !== 'undefined') option.value = option[item.form.child.props.value];
                            });
                        }
                        return list;
                    }
                }
            }
        },
        /**
         * 表单显示
         */
        formItemShow(item) {
            let self = this;
            let show = true;
            // 不显示
            if (item.form === false) {
                show = false;
            }
            if (typeof item.form.show != 'undefined' && item.form.show === false) {
                show = false;
            }
            // 主键不显示
            if (item.prop === self.rowKey) {
                show = false;
            }
            // 更新时不显示
            if (self.drawerData[self.rowKey] !== '' && item.form.update === false) {
                show = false;
            }
            // 新增时不显示
            if (self.drawerData[self.rowKey] === '' && item.form.save === false) {
                show = false;
            }
            // 关联显示
            if (typeof item.form.relation === 'object') {
                item.form.relation.forEach(function(val, index) {
                    if (typeof val.where != 'undefined' && val.where == '<>') {
                        if (self.drawerData[val.prop] === val.value) {
                            show = false;
                        }
                    } else {
                        if (self.drawerData[val.prop] !== val.value) {
                            show = false;
                        }
                    }
                })
            }
            return show;
        },
    },
    watch: {
        search() {
            this.getData();
        },
        drawerData() {
            this.formRules();
        },
        drawer(val) {
            if(!val){
                this.$refs.drawerData.clearValidate();
            }
            this.randId = common.id(16);
        },
    }
});

/**
 * 树形菜单
 */
Vue.component('el-tree-menu', {
    template: `
    <div class="el-tree-menu">
        <el-checkbox 
            @change="checkedAll()" 
            v-model="isChecked"
            label="全部选中" 
            size="mini" 
            border>
        </el-checkbox>
        <el-checkbox 
            @change="expandAll()"
            v-model="expand"
            label="全部展开" 
            size="mini" 
            border>
        </el-checkbox>
        <el-tree
            node-key="id"
            ref="tree"
            :data="data"
            :props="{children: 'children', label: 'title'}"
            :expand-on-click-node="false"
            @check="checkChange()"
            show-checkbox
            check-on-click-node>
        </el-tree>
    </div>
    `,
    props: {
        value: {
            type: Array,
            default: []
        },
        list: {
            type: Array,
            default: []
        }
    },
    data() {
        return {
            data: tree.convert(this.list),
            isChecked: false,
            expand: false,
        }
    },
    created() {
        let self   = this;
        self.$nextTick(() => {
            var arr    = self.value;
            var newArr = [];
            var item   = '';
            arr.forEach(item=> {
                self.checked(item,self.data,newArr)
            })
            self.$refs.tree.setCheckedKeys(newArr);
        })
    },
    methods: {
        /**
         * 后台数据回显
         */
        checked(id,data,newArr) {
            let self = this;
            data.forEach(item => {
                if(item.id == id){
                    if (typeof item.children === 'undefined') {
                        newArr.push(item.id)
                    }
                }else{
                    if (typeof item.children !== 'undefined') {
                        self.checked(id,item.children,newArr)
                    }
                }
            });
        },
        /**
         * 全选
         */
        checkedAll() {
            let keys = this.isChecked ? common.arrayColumn(this.list, 'id') : [];
            this.$refs.tree.setCheckedKeys(keys);
            this.$emit('input', keys);
        },
        /**
         * 展开
         */
        expandAll() {
            let self = this;
            let keys = common.arrayColumn(self.list, 'id');
            keys.forEach(function(val, key){
                if (self.$refs.tree.store.nodesMap[val].childNodes.length > 0) {
                    self.$refs.tree.store.nodesMap[val].expanded = self.expand;
                }
            })
        },
        /**
         * 状态改变
         */
        checkChange() {
            let roleSel = this.$refs.tree.getCheckedKeys();
            let rolePar = this.$refs.tree.getHalfCheckedKeys();
            this.$emit('input', roleSel.concat(rolePar));
        }
    },
    watch: {
        value(val){
            let self   = this;
            self.$nextTick(() => {
                var arr    = self.value;
                var newArr = [];
                var item   = '';
                arr.forEach(item=> {
                    self.checked(item,self.data,newArr)
                })
                self.$refs.tree.setCheckedKeys(newArr);
            })
        }
    }
})
/**
 * 自定义数组
 */
Vue.component('el-array', {
    template: `
    <div class="el-array" ref="elArray">
        <el-button type="primary" @click="pushTable()" size="small">新增一行</el-button>
        <el-table :data="table" row-key="id" border>
            <el-table-column v-for="(item, index) in column" :prop="item" :key="index" :width="item.type.width">
                <template slot="header" slot-scope="scope">
                    <el-tooltip :content="item.field" placement="top">
                        <span class="el-array-title">{{item.label}}</span>
                    </el-tooltip>
                    <el-tooltip content="删除字段" placement="top">
                        <i class="el-array-remove el-icon-delete" @click="delField(index)"></i>
                    </el-tooltip>
                </template>
                <template slot-scope="scope">
                    <component v-model="scope.row[item.field]" :is="item.type.is" :type="item.type.type"></component>
                </template>
            </el-table-column>
            <el-table-column width="104">
                <template slot="header" slot-scope="scope">
                    <el-button type="primary" @click="dialog = true" size="mini">新增字段</el-button>
                </template>
                <template slot-scope="scope">
                    <el-button type="danger" icon="el-icon-delete" size="mini" @click="removeTable(scope.$index)" circle></el-button>
                    <el-button type="primary" icon="el-icon-plus" size="mini" @click="pushTable(scope.$index)" circle></el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-dialog top="20px" title="列表新增字段" :visible.sync="dialog" :close-on-click-modal="false" width="500px" append-to-body>
            <el-form :model="fieldForm" :rules="rules" ref="fieldForm" label-width="100px" @submit.native.prevent>
                <el-form-item label="字段类型：" prop="type">
                    <el-select style="width:100%" v-model="fieldForm.type" value-key="label" placeholder="请选择字段类型" filterable>
                        <el-option v-for="(item, index) in fieldList" :key="index" :label="item.label" :value="item"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="字段标题：" prop="label">
                    <el-input v-model="fieldForm.label" placeholder="如：内容"></el-input>
                </el-form-item>
                <el-form-item label="字段变量：" prop="field">
                    <el-input v-model="fieldForm.field" placeholder="如：content"></el-input>
                </el-form-item>
                <el-form-item label="字段宽度：" prop="width">
                    <el-input v-model="fieldForm.type.width" placeholder="如：500px"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button size="small" type="primary" @click="addField()">确 定</el-button>
                    <el-button size="small" @click="dialog = false">取 消</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
    `,
    props: {
        value: {
            type: Object,
            default: {},
        },
        type: {
            type: String,
            default: '',
        },
    },
    data() {
        var validateRepeatName = (rule, value, callback) => {
            let isExist = common.arrayIndex(this.column, this.fieldForm.field, 'field');
            if(isExist !== -1) {
                callback(new Error('字段变量不能重复！'));
            } else {
                callback();
            }
        }
        return {
            table: [],
            column: {},
            dialog: false,
            fieldForm: {
                label: '',
                field: '',
                type: '',
            },
            rules: {
                label: [
                    { required: true, message: '请填写字段标题', trigger: 'blur' },
                ],
                field: [
                    { required: true, message: '请填写字段变量', trigger: 'blur' },
                    { pattern: /^[a-zA-Z][a-zA-Z0-9_]*$/, message: '以字母开头只能输入字母、下划线、数字', trigger: 'blur' },
                    { validator: validateRepeatName, trigger: 'blur' },
                ],
                type: [
                    { required: true, message: '请选择字段类型', trigger: 'blur' },
                ],
            },
            fieldList: [
                {label: '文本', is: 'el-input', width:'200px', value: ''},
                {label: '文本域', is: 'el-input', type: 'textarea', width:'300px', value: ''},
                {label: '编辑器', is: 'el-editor', width:'700px', value: ''},
                {label: '文件上传', is: 'el-file-select', type: 'all', width:'100px', value: ''},
                {label: '链接', is: 'el-link-select', width:'300px', value: {}}
            ],
        }
    },
    created() {
        let bool = JSON.stringify(this.value) == "{}" || this.value == '';
        this.table = bool ?  [] : this.value.table;
        this.column = bool ? [{label: '标题', field: 'title', type: {label: '文本', is: 'el-input', value: ''}}] : this.value.column;
    },
    methods: {
        /**
         * 添加行
         */
        pushTable(index = "") {
            let ob = {};
            this.column.forEach( function (item, index) {
                ob[item.field] = item.type.value;
            });
            index === "" ? this.table.push(ob) : this.table.splice(index + 1, 0, ob);
        },
        /**
         * 删除数组
         */
        removeTable(index){
            this.table.splice(index, 1);
        },
        /**
         * 新增字段
         */
        addField() {
            let self = this;
            self.$refs.fieldForm.validate((valid) => {
                if (valid) {
                    let row = JSON.parse(JSON.stringify(self.fieldForm));
                    self.column.push(row);
                    self.table.forEach(function (item, index) {
                        self.$set(item, row.field, row.type.value);
                    });
                    self.dialog = false;
                    self.$refs.fieldForm.resetFields();
                } else {
                    return false;
                }
            });
        },
        /**
         * 删除字段
         */
        delField(index) {
            let self = this;
            let prop = self.column[index]['field'];
            self.column.splice(index, 1);
            self.table.forEach(function(item, index) {
                self.$delete(item, prop);
            });
        },
    },
    watch: {
        column(){
            this.$emit('input', {column: this.column, table: this.table});
        },
        table() {
            this.$emit('input', {column: this.column, table: this.table});
        },
    }
});
/**
 * 图片水印弹窗
 */
Vue.component('el-watermark', {
    template: `
    <div class="el-watermark">
        <el-checkbox v-model="watermark.open" :false-label="0" :true-label="1" @change="save()" style="margin-right:10px">开启水印</el-checkbox>
        <el-button
            size="small" 
            icon="el-icon-setting" 
            @click="dialog = true" 
            plain>
            水印设置
        </el-button>
        <el-dialog
            class="el-watermark-dialog"
            top="16px"
            title="设置水印"
            :visible.sync="dialog"
            width="600px"
            :close-on-click-modal="false"
            append-to-body>
            <el-form label-width="100px" style="height: 650px">
                <el-form-item label="水印类型：">
                    <el-radio-group size="small" v-model="watermark.type">
                        <el-radio label="font">文字</el-radio>
                        <el-radio label="image">图片</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form label-width="180px">
                    <template v-if="watermark.type === 'font'">
                        <el-form-item label="文字内容：">
                            <el-input class="el-watermark-text" size="small" v-model="watermark.fontText" placeholder=""></el-input>
                        </el-form-item>
                        <el-form-item label="文字样式：">
                            <el-select size="small" v-model="watermark.fontFamily">
                                <el-option
                                    v-for="(item, index) in fontFamily"
                                    :key="index"
                                    :label="item.label"
                                    :value="item.value">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="文字大小：">
                            <el-select size="small" v-model="watermark.fontSize">
                                <el-option
                                    v-for="(item, index) in fontSize"
                                    :key="index"
                                    :value="item">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="文字颜色：">
                            <el-color-picker
                                size="small"
                                v-model="watermark.fontColor"
                                :predefine="predefineColors"
                                color-format="hex">
                            </el-color-picker>
                        </el-form-item>
                        <el-form-item class="el-watermark-angle" label="文字倾斜：">
                            <el-slider v-model="watermark.fontAngle" show-input></el-slider>
                        </el-form-item>
                    </template>
                    <template v-else>
                        <el-form-item class="el-watermark-angle" label="水印图片：">
                            <el-upload
                                class="el-watermark-uploader"
                                :action="admin_url('file/uploadAppoint')"
                                :data="{name: 'watermark', ext: 'png'}"
                                :show-file-list="false"
                                :on-success="watermarkSuccess"
                                :before-upload="watermarkUpload"
                                accept=".png">
                                <el-image :src="watermark.image"></el-image>
                            </el-upload>
                            只允许.png格式图片
                        </el-form-item>
                    </template>
                </el-form>
                <el-form-item label="水印大小：">
                    <el-radio-group size="small" v-model="watermark.sizeType">
                        <el-radio label="actual">按实际大小</el-radio>
                        <el-radio label="scale">按比例缩放</el-radio>
                    </el-radio-group>
                    <el-select
                        style="margin-left: 20px"
                        v-if="watermark.sizeType === 'scale'" 
                        size="small" 
                        v-model="watermark.scale">
                        <el-option
                            v-for="(item, index) in scale"
                            :key="index"
                            :label="item + '%'"
                            :value="item">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="水印位置：">
                    <el-col :span="8">
                        <el-select size="small" v-model="watermark.position">
                            <el-option
                                v-for="(item, index) in position"
                                :key="index"
                                :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                    </el-col>
                    <el-col :span="15" :offset="1">
                        不透明度：
                        <el-select size="small" v-model="watermark.opacity">
                            <el-option
                                v-for="(item, index) in opacity"
                                :key="index"
                                :label="item + '%'"
                                :value="item">
                            </el-option>
                        </el-select>
                    </el-col>
                </el-form-item>
                <el-form-item label="示例图片：">
                    <div class="el-watermark-example-image">
                        <div slot="error" class="el-watermark-image-slot">
                            <i class="el-icon-picture-outline"></i>
                        </div>
                        <div 
                            class="el-watermark-example" 
                            :class="'el-watermark-position' + watermark.position"
                            :style="{transform: watermarkFontAngle,'-webkit-transform': watermarkFontAngle,'-moz-transform': watermarkFontAngle}">
                            <span
                                v-if="watermark.type === 'font'"
                                :style="{opacity: watermarkOpacity, fontSize: watermarkFontSize, color: watermark.fontColor,fontFamily:watermark.fontFamily}">
                                {{watermark.fontText}}
                            </span>
                            <img
                                v-else
                                style="max-width: 100%;max-height: 100%;"
                                :src="watermark.image"
                                :style="{opacity: watermarkOpacity, width: watermarkImageWidth}">
                        </div>
                    </div>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialog = false" size="small">关 闭</el-button>
                <el-button type="primary" @click="save()" :loading="saveLoading" size="small">保 存</el-button>
            </div>
        </el-dialog>
    </div>`,
    props: {
        value: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            watermark: watermark,
            saveLoading: false,
            dialog: false,
            updateUrl: "file/watermark",
            scale: ["5","10","15","20","25","30","35","40","45","50"],
            opacity: ["10","20","30","40","50","60","70","80","90","100"],
            fontSize: ["12","14","16","18","20","24","28","32","36","40","48","56","64","72","80"],
            position: [ 
                {label: "左上角", value: "1"},
                {label: "正上方", value: "2"},
                {label: "右上角", value: "3"},
                {label: "左中处", value: "4"},
                {label: "正中处", value: "5"},
                {label: "右中处", value: "6"},
                {label: "左下角", value: "7"},
                {label: "正下方", value: "8"},
                {label: "右下角", value: "9"},
            ],
            fontFamily: [ 
                {label: "默认字体", value: "/admin/fonts/FZHTJW.ttf"},
            ],
            predefineColors: [
                '#333333',
                '#ff8c00',
                '#ffd700',
                '#90ee90',
                '#00ced1',
                '#1e90ff',
                '#c71585',
            ],
        }
    },
    computed:{
        watermarkOpacity() {
            return this.watermark.opacity/100;
        },
        watermarkImageWidth() {
            return this.watermark.sizeType === 'actual' ? '': parseInt((this.watermark.scale/100) * 280) + 'px';
        },
        watermarkFontSize() {
            return this.watermark.sizeType === 'actual' ? this.watermark.fontSize + 'px': parseInt((this.watermark.scale/100) * (150/2)) + 'px';
        },
        watermarkFontAngle() {
            var position = '';
            switch(this.watermark.position){
                case ("2"):
                case ("8"):
                    position = 'translateX(-50%)';
                break;
                case ("4"):
                case ("6"):
                    position = 'translateY(-50%)';
                break;  
                case ("5"):
                    position = 'translate(-50%,-50%)';
                break;
            }
            return this.watermark.type === 'font' ? `rotate(-${this.watermark.fontAngle}deg) ${position}` : position;
        },
    },
    methods: {
        /**
         * 保存数据
         */
        save() {
            let self = this;
            if (self.watermark.type !== "image" || self.watermark.image !== "") {
                self.saveLoading = true;
                request.post(self.updateUrl, {'value':self.watermark}, function(res) {
                    self.saveLoading = false;
                    self.dialog  = false;
                    if(res.status !== 'success') {
                        self.$notify({ showClose: true, message: res.message, type: res.status});
                    }
                });
            } else {
                self.$notify({ showClose: true, message: "请上传水印图片", type: "error"});
            }
        },
        /**
         * 设置成功
         */
        watermarkSuccess(res, file) {
            if (res.status === 'success') {
                this.watermark.image = res.url + '?' + Math.floor(Math.random()*10000000);
            } else {
                this.$notify.error(res.message);
            }
        },
        /**
         * 上传水印图
         */
        watermarkUpload(file) {
            const isJPG  = file.type === 'image/png';
            const isLt2M = file.size / 1024 / 1024 < 1;
            if (!isJPG) {
                this.$notify.error('上传水印图片只能是 png 格式!');
            }
            if (!isLt2M) {
                this.$notify.error('上传水印图片大小不能超过 1MB!');
            }
            return isJPG && isLt2M;
        }
    },
});
/**
 * 文件列表
 */
Vue.component('el-file-list', {
    template: `
    <el-container class="el-file-list el-layout">
        <el-aside width="192px">
            <el-tabs :tab-position="document.body.clientWidth > 768 ? 'left' : 'top'" v-model="typeIndex" @tab-click="typeChange">
                <el-tab-pane v-for="(item, index) in side" :key="index" :name="String(index)">
                    <span slot="label"><i class="iconfont" :class="item.icon"></i>{{item.title}}</span>
                </el-tab-pane>
            </el-tabs>
        </el-aside>
        <el-container>
            <div class="el-content">
                <div class="el-file-header">
                    <el-watermark v-show="search.type == 'image' || search.type == '' || search.type == 'all'"></el-watermark>
                    <el-button size="small" icon="el-icon-refresh-right" @click="refresh()" plain>刷新</el-button>
                    <el-upload v-show="search.type !== 'recycle'" class="el-upload" :accept="accept" :show-file-list="false" :action="admin_url(addUrl)" :before-upload="beforeUpload" :on-progress="progressUpload" :on-success="successUpload" :on-error="errorUpload" :data="{theme: theme}" multiple>
                        <el-button size="small" icon="el-icon-upload2" plain>上传</el-button>
                    </el-upload>
                    <el-button v-show="search.type !== 'recycle'" size="small" icon="el-icon-delete" :disabled="rows.length === 0" @click="recovery()" plain>放入回收站</el-button>
                    <el-button v-show="search.type === 'recycle'" size="small" icon="el-icon-delete" @click="emptyTrash()" plain>清空</el-button>
                    <el-button v-show="search.type === 'recycle'" size="small" icon="el-icon-wallet" :disabled="rows.length === 0" @click="reduction()" plain>还原</el-button>
                    <el-button v-show="search.type === 'recycle'" size="small" icon="el-icon-delete" :disabled="rows.length === 0" @click="remove()" plain>删除</el-button>
                    <el-input size="small" suffix-icon="el-icon-search" placeholder="搜索文件" v-model="search.keyword" @change="keywordChange"></el-input>
                    <span :class="show == 'flat' ? 'iconfont icon-caidan2' : 'iconfont icon-caidan1'" @click="show = show == 'flat' ? 'table' : 'flat'" title="切换模式"></span>
                    <el-dropdown @command="propChange">
                        <span class="el-dropdown-link">
                            <span class="iconfont icon-jiangxu3"></span>
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item command="size">大小</el-dropdown-item>
                            <el-dropdown-item command="title">文件名</el-dropdown-item>
                            <el-dropdown-item command="create_time">修改日期</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
                <div class="theader">
                    <div class="file">
                        <el-checkbox v-model="checkAll">
                            <template v-if="rows.length > 0">已选中{{rows.length}}个</template>
                            <template v-else><span class="title">文件名</span></template>
                        </el-checkbox>
                        <span class="caret-wrapper" :class="{active: search.prop === 'title'}">
                            <i class="sort-caret asc" :class="{active: search.order === 'asc'}" @click="orderChange('title', 'asc')"></i>
                            <i class="sort-caret desc" :class="{active: search.order === 'desc'}" @click="orderChange('title', 'desc')"></i>
                        </span>
                    </div>
                    <template v-if="show == 'table'">
                        <div class="size">
                            <span class="title" @click="orderChange('size', search.order === 'asc' ? 'desc' : 'asc')">大小</span>
                            <span class="caret-wrapper" :class="{active: search.prop === 'size'}">
                                <i class="sort-caret asc" :class="{active: search.order === 'asc'}" @click="orderChange('size', 'asc')"></i>
                                <i class="sort-caret desc" :class="{active: search.order === 'desc'}" @click="orderChange('size', 'desc')"></i>
                            </span>
                        </div>
                        <div class="time">
                            <span class="title" @click="orderChange('create_time', search.order === 'asc' ? 'desc' : 'asc')">修改日期</span>
                            <span class="caret-wrapper" :class="{active: search.prop === 'create_time'}">
                                <i class="sort-caret asc" :class="{active: search.order === 'asc'}" @click="orderChange('create_time', 'asc')"></i>
                                <i class="sort-caret desc" :class="{active: search.order === 'desc'}" @click="orderChange('create_time', 'desc')"></i>
                            </span>
                        </div>
                    </template>
                </div>
                <div class="list" :class="show" v-infinite-scroll="getData" :infinite-scroll-disabled="disabled">
                    <el-empty v-if="list.length === 0 && loading === false" description="空空如也~"></el-empty>
                    <template v-else>
                        <div class="item" v-for="(item, index) in table" :key="index" @click="checked(item)" :class="{active: item.check}">
                            <div class="file">
                                <el-checkbox v-model="item.check" disabled></el-checkbox>
                                <div class="cover">
                                    <img v-if="typeof item.percentage == 'undefined'" class="image" :src="item.cover" />
                                    <el-progress v-else type="circle" :percentage="item.percentage"></el-progress>
                                </div>
                                <div class="title" @click.stop="preview(item, index)">
                                    <el-input v-if="item.rename" v-model="item.ctitle" :ref="'title' + index" @blur="renameConfirm(item)" @keyup.enter.native="renameConfirm(item)">
                                        <template slot="append">
                                            <i class="el-icon-check" @click.stop="renameConfirm(item)"></i>
                                            <i class="el-icon-close" @click.stop="renameClose()"></i>
                                        </template>
                                    </el-input>
                                    <span v-else>{{item.title}}</span>
                                    <el-image 
                                        v-show="false" 
                                        :ref="'preview' + index" 
                                        :src="item.url" 
                                        :preview-src-list="previewImages">
                                    </el-image>
                                </div>
                                <div class="operation" v-if="item.rename === false">
                                    <span v-show="search.type !== 'recycle'" @click.stop="renameClick(item, index)" class="el-icon-edit" title="重命名"></span>
                                    <span @click.stop="downloadClick(item)" class="el-icon-download" title="下载"></span>
                                    <span v-show="search.type !== 'recycle'" @click.stop="recovery(item)" class="el-icon-delete" title="删除"></span>
                                    <span v-show="search.type === 'recycle'" @click.stop="reduction(item)" class="el-icon-folder-checked" title="还原文件"></span>
                                    <span v-show="search.type === 'recycle'" @click.stop="remove(item)" class="el-icon-delete" title="彻底删除"></span>
                                </div>
                            </div>
                            <div class="size">{{item.size}}</div>
                            <div class="time">{{item.create_time}}</div>
                        </div>
                    </template>
                    <p class="el-bottom-loading" v-if="loading"><i class="el-icon-loading"></i><span>拼命加载中...</span></p>
                    <p class="el-bottom-loading" v-if="noMore && count > 0"><span>主人，我已经到底啦！</span></p>
                </div>
            </div>
        </el-container>
    </el-container>
    `,
    props: {
        value: {
            type: Array,
            default: [],
        },
        limit: {
            type: Number,
            default: -1,
        },
        type: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            show: document.body.clientWidth > 768 ? 'table' : 'flat',
            list: [],
            rows: this.value,
            loading: false,
            checkAll: false,
            indexUrl: "file/index",
            addUrl: "file/upload",
            delUrl: "file/delete",
            editUrl: "file/update",
            recoveryUrl: "file/recovery",
            reductionUrl: "file/reduction",
            emptyTrashUrl: "file/emptyTrash",
            search: {
                keyword: '',
                type: this.type,
                page: 1,
                prop: 'create_time',
                order: 'desc',
            },
            typeIndex: "0",
            typeList: [
                {name: "all", title: "全部", icon: 'icon-quanbuwenjian', accept: '.png,.jpg,.jpeg,.bmp,.gif,.ico,.mp4,.mp3,.docx,.doc,.swf,.psd,.css,.js,.html,.exe,.dll,.zip,.rar,.ppt,.pdf,.xlsx,.xls,.txt,.torrent,.dwt,.sql,.svg'},
                {name: "image", title: "图片", icon: 'icon-xingzhuang-tupian', accept: '.png,.jpg,.jpeg,.bmp,.gif,.ico'},
                {name: "video", title: "视频", icon: 'icon-shipin', accept: '.mp4'},
                {name: "audio", title: "音乐", icon: 'icon-yinle', accept: '.mp3'},
                {name: "word",  title: "文档", icon: 'icon-icon_shiyongwendang', accept: '.docx,.doc'},
                {name: "other", title: "其它", icon: 'icon-wenjian', accept: '.swf,.psd,.css,.js,.html,.exe,.dll,.zip,.rar,.ppt,.pdf,.xlsx,.xls,.txt,.torrent,.dwt,.sql,.svg'},
            ],
            propList: [
                { title: "按修改时间排序", name: "create_time"},
                { title: "按文件大小排序", name: "size"},
                { title: "按文件名排序", name: "title"},
            ],
        }
    },
    computed:{
        table() {
            let self = this;
            let list = self.list;
            list.forEach( function (item, key) {
                let index  = common.arrayIndex(self.rows, item.id, 'id');
                item.check = index === -1 ? false : true;
            })
            return list;
        },
        side() {
            switch(this.type) {
                case '':
                    let side = this.typeList;
                    side.push({name: 'recycle', title: "回收站", icon: 'icon-huishouzhan'});
                    return side;
                break;
                case 'all':
                    return this.typeList;
                break;
                default:
                    let index = common.arrayIndex(this.typeList, this.type, 'name');
                    return [this.typeList[index]];
            }
        },
        name() {
            return this.side[this.typeIndex]['name'];
        },
        accept() {
            return this.side[this.typeIndex]['accept'];
        },
        noMore() {
            return this.count <= this.list.length;
        },
        disabled() {
            return this.loading || this.noMore;
        },
        previewImages() {
            let arr = [];
            this.list.forEach( function (item, index) {
                if (item.type === 'image') {
                    arr.push(item.url);
                }
            })
            return arr;
        },
    },
    methods: {
        /**
         * 获取数据
         */
        getData() {
            let self     = this;
            self.loading = true;
            request.post(self.indexUrl, self.search, function(res) {
                res.data.forEach(function (item, index) {
                    item = self.initData(item);
                })
                self.search.page++;
                self.loading = false;
                self.list    = self.list.concat(res.data);
                self.count   = res.count;
            });
        },
        /**
         * 初始化数据
         */
        initData(item) {
            let cover      = '/admin/images/filecover/';
            let type       = file.type(item.url)['name'];
            let name       = item.url.substring(item.url.lastIndexOf('/') + 1, item.url.lastIndexOf('.'));
            item['size']   = file.size(item.size);
            item['type']   = type;
            if (item['cover']  = type === 'image') {
                let suffix = item.url.substring(item.url.lastIndexOf('.') + 1);
                if (suffix != 'gif' && suffix != 'ico') {
                    item['cover']  = item.url.replace(name, name + '100x100');
                } else {
                    item['cover']  = item.url;
                }
            } else {
                item['cover']  = cover + type + '.png';
            }
            item['rename'] = false;
            item['ctitle'] = item.title;
            return item;
        },
        /**
         * 选择
         */
        checked(item) {
            if (typeof item.percentage == 'undefined') {
                let index = common.arrayIndex(this.rows, item.id, 'id');
                if (index === -1) {
                    this.rows.push(item);
                } else {
                    this.rows.splice(index , 1);
                }
            }
        },
        /**
         * 类型
         */
        typeChange() {
            this.search = Object.assign({}, this.search, {page: 1, type: this.name, keyword: ''});
        },
        /**
         * 刷新
         */ 
        refresh() {
            this.search  = Object.assign({}, this.search, {page: 1, keyword: ''});
        },
        /**
         * 搜索
         */
        keywordChange() {
            this.search  = Object.assign({}, this.search, {page: 1});
        },
        /**
         * 名称排序
         */
        propChange(prop) {
            this.search  = Object.assign({}, this.search, {page: 1, prop: prop, order: 'desc'});
        },
        /**
         * 升降排序
         */
        orderChange(prop, order) {
            this.search  = Object.assign({}, this.search, {page: 1, prop: prop, order: order});
        },
        /**
         * 预览
         */
        preview(item, index) {
            if (item.rename === false) {
                let type = file.type(item.url);
                if (type.name === 'image') {
                    this.$refs['preview' + index][0].clickHandler();
                } else {
                    window.open(item.url);
                }
            }
        },
        /**
         * 点击下载
         */
        downloadClick(item) {
            window.open(admin_url('file/download', {title: item.title, url: item.url}));
        },
        /**
         * 点击重命名
         */
        renameClick(item, index) {
            let self = this;
            self.renameClose();
            item.rename = true;
            self.$nextTick(()=>{
                self.$refs[`title${index}`][0].focus();
                self.$refs[`title${index}`][0].select();
            });
        },
        /**
         * 取消重命名
         */
        renameClose() {
            this.list.forEach(function(item) {
                item.ctitle = item.title;
                item.rename = false;
            })
        },
        /**
         * 确定重命名
         */
        renameConfirm(item) {
            let self = this;
            request.post(self.editUrl, {id: item.id, title: item.ctitle}, function(res) {
                if (res.status === 'success') {
                    item.title = item.ctitle;
                    self.renameClose();
                } else {
                    self.$notify({ showClose: true, message: res.message, type: res.status});
                }
            });
        },
        /**
         * 放入回收站
         */
        recovery(item = "") {
            let self = this;
            let rows = item === "" ? self.rows : [item];
            let ids  = common.arrayColumn(rows, 'id');
            self.$confirm('将文件放入回收站', '', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                request.post(self.recoveryUrl, {ids: ids}, function(res){
                    if(res.status === 'success') {
                        self.refresh();
                    } else {
                        self.$notify({ showClose: true, message: res.message, type: res.status});
                    }
                });
            }).catch(() => {});
        },
        /**
         * 彻底删除
         */
        remove(item = "") {
            let self = this;
            let rows = item === "" ? self.rows : [item];
            let ids  = common.arrayColumn(rows, 'id');
            self.$confirm('彻底删除后文件无法找回，确定删除吗？', '', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'error'
            }).then(() => {
                request.post(self.delUrl, {ids: ids}, function(res){
                    if(res.status === 'success') {
                        self.refresh();
                    } else {
                        self.$notify({ showClose: true, message: res.message, type: res.status});
                    }
                });
            }).catch(() => {});
        },
        /**
         * 还原文件
         */
        reduction(item = "") {
            let self = this;
            let rows = item === "" ? self.rows : [item];
            let ids  = common.arrayColumn(rows, 'id');
            request.post(self.reductionUrl, {ids: ids}, function(res){
                if(res.status === 'success') {
                    self.refresh();
                } else {
                    self.$notify({ showClose: true, message: res.message, type: res.status});
                }
            });
        },
        /**
         * 清空回收站
         */
        emptyTrash() {
            let self       = this;
            self.$confirm('清空回收站后所有文件无法找回，确定清空吗？', '', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'error'
            }).then(() => {
                request.post(self.emptyTrashUrl, {}, function(res){
                    if(res.status === 'success') {
                        self.refresh();
                    } else {
                        self.$notify({ showClose: true, message: res.message, type: res.status});
                    }
                });
            }).catch(() => {});
        },
        /**
         * 上传文件开启
         */
        beforeUpload(item) {
            let fileItem = {
                url: '',
                uid: item.uid, 
                size: file.size(item.size),
                title: item.name, 
                status: 1, 
                rename: false, 
                percentage: 0, 
                create_time: common.dateTime(), 
            };
            this.list.splice(0, 0, fileItem);
        },
        /**
         * 上传文件中
         */
        progressUpload(event, item) {
            let index = common.arrayIndex(this.list, item['uid'], 'uid');
            let row = this.list[index];
            row.percentage = Math.round(item.percentage);
            this.$set(this.list, index, row);
        },
        /**
         * 上传成功回调
         */
        successUpload(res, item) {
            let self = this;
            let index = common.arrayIndex(self.list, item['uid'], 'uid');
            if (res.status === 'success') {
                let row = self.list[index];
                row.percentage = 100;
                self.$set(self.list, index, row);
                setTimeout(() => {
                    self.list.splice(index, 1, self.initData(res.data));
                }, 500);
            } else {
                self.list.splice(index,1);
                self.$notify({showClose: true, message: res.message, type: 'error'});
            }
        },
        /**
         * 上传错误回调
         */
        errorUpload(res, item) {
            let index = common.arrayIndex(this.list, item['uid'], 'uid');
            this.list.splice(index,1);
            this.$notify({showClose: true, message: '系统错误！', type: 'error'});
        },
    },
    watch: {
        rows(val) {
            this.$emit('input', val);
        },
        value(val) {
            this.rows = val;
        },
        search() {
            this.checkAll = false;
            this.list     = [];
            this.rows     = [];
            this.getData();
        },
        checkAll(val) {
            this.rows = val ? JSON.parse(JSON.stringify(this.list)) : [];
        },
    }
});
/**
 * 文件弹窗选择
 */
Vue.component('el-file-dialog', {
    template: `
    <el-dialog v-if="dialog" top="20px" width="925px" class="el-file-dialog" title="文件选择" :visible.sync="dialog" :close-on-click-modal="false" :modal="false">
        <el-file-list :type="type" v-model="selected"></el-file-list>
        <div class="el-file-selected">
            <div class="el-limit">
                <template v-if="limit === 0">无限制</template>
                <template v-else>限制<span>&nbsp;{{selected.length}}&nbsp;/&nbsp;{{limit}}</span></template>
            </div>
            <div class="el-list-selected">
                <div class="el-item-selected" title="查看大图" v-for="(item, index) in selected" :key="index" @click="preview(item, index)">
                    <img :src="item.cover"/>
                    <el-image :src="item.url" :ref="'preview' + index" :preview-src-list="previewImages" style="display:none"></el-image>
                    <i class="el-icon-close el-file-remove" @click.stop="remove(index)"></i>
                </div>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="dialog = false" size="small">取 消</el-button>
            <el-button type="primary" @click="success" size="small">确 定</el-button>
        </span>
    </el-dialog>`,
    props: {
        list: {
            type: Array,
            default: [],
        },
        type: {
            type: String,
            default: 'image',
        },
        limit:{
            type: Number,
            default: 1, 
        },
        value: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            dialog: this.value,
            selected: JSON.parse(JSON.stringify(this.list)),
        }
    },
    computed: {
        previewImages() {
            let arr = [];
            this.selected.forEach( function (item, index) {
                if (item.type === 'image') {
                    arr.push(item.url);
                }
            })
            return arr;
        },
    },
    methods: {
        preview(item, index) {
            if (item.rename === false) {
                let type = file.type(item.url);
                if (type.name === 'image') {
                    this.$refs['preview' + index][0].clickHandler();
                } else {
                    window.open(item.url);
                }
            }
        },
        success() {
            this.dialog = false;
            this.$emit('success-selected',this.selected);
        },
        remove(index) {
            this.selected.splice(index, 1);
        },
    },
    watch: {
        value(val) {
            this.dialog = val;
        },
        dialog(val) {
            this.$emit('input', val);
        },
        selected(val) {
            if (val.length > this.limit && this.limit != 0) {
                this.selected = val.slice(val.length - this.limit);
            }
        },
    }
});
/**
 * 多文件选择
 */
Vue.component('el-file-list-select', {
    template: `
    <div class="el-file-list-select">
        <div class="upload" @click="dialog = true" >
            <i class="el-icon-plus el-push"></i>
        </div>
        <draggable class="draggable" v-model="list" v-bind="draggable">
            <div class="item" v-for="(item, index) in list" :key="index" @click="preview(item, index)" title="拖动排序">
                <img :src="item.cover"/>
                <el-image :src="item.url" :ref="'preview' + index" :preview-src-list="previewImages" style="display:none"></el-image>
            </div>
        </draggable>
        <el-file-dialog v-model="dialog" :list="list" :type="type" :limit="0" @success-selected="fileSuccess($event)"></el-file-dialog>
    </div>
    `,
    props: {
        value: {
            type: Array,
            default: [],
        },
        type: {
            type: String,
            default: 'image'
        },
    },
    data() {
        return {
            list: this.value,
            dialog: false,
            draggable: {
                handle: '.item',
                animation: 300,
                forceFallback: true,
            }
        }
    },
    computed: {
        previewImages() {
            let arr = [];
            this.list.forEach( function (item, index) {
                if (item.type === 'image') {
                    arr.push(item.url);
                }
            })
            return arr;
        },
    },
    methods: {
        /**
         * 文件预览
         */
        preview(item, index) {
            let type = file.type(item.url);
            if (type.name === 'image') {
                this.$refs['preview' + index][0].clickHandler();
            } else {
                window.open(item.url);
            }
        },
        /**
         * 文件选择成功
         */
        fileSuccess(list) {
            this.list = list;
        },
    },
    watch: {
        value(val) {
            this.list = val;
        },
        list(val) {
            this.$emit('input', val);
        },
    }
});
/**
 * 单文件选择
 */
Vue.component('el-file-select', {
    template: `
    <div class="el-file-select">
        <div class="el-file-select-warp" :style="{height: size + 'px', width: size + 'px'}">
            <el-image ref="preview" :preview-src-list="[cover]" :src="cover" :style="{lineHeight: size + 'px'}">
                <div slot="error" class="image-slot">
                    <img class="error-image" src="/admin/images/error.png"/>
                </div>
            </el-image>
            <i class="el-icon-close el-remove" @click="file = ''"></i>
            <div class="tips">
                <div class="icon">
                    <i class="el-icon-folder-opened" title="从资源库中选择" @click="dialog = true"></i>
                    <i class="el-icon-search" title="查看大图" @click="preview()"></i>
                </div>
            </div>
        </div>
        <el-file-dialog title="文件选择" v-model="dialog" :list="[]" :type="type" :limit="1" @success-selected="success($event)"></el-file-dialog>
    </div>`,
    props: {
        value: {
            type: String,
            default: ''
        },
        type: {
            type: String,
            default: 'image'
        },
        size: {
            type: String,
            default: 50,
        }
    },
    data() {
        return {
            file: this.value,
            dialog: false,
        }
    },
    computed: {
        fileType() {
            let type = file.type(this.file);
            return type.name;
        },
        cover() {
            if (this.type === 'image') {
                return this.file;
            } else {
                return this.fileType === 'image' ? this.file : '/admin/images/filecover/' + this.fileType + '.png';
            }
        },
    },
    methods: {
        preview() {
            if (this.type === 'image') {
                this.$refs.preview.clickHandler();
            } else {
                if (this.fileType === 'image') {
                    this.$refs.preview.clickHandler();
                } else {
                    window.open(this.file);
                }
            }
        },
        success(list) {
            this.file = list[0]['url'];
        },
    },
    watch: {
        file(val) {
            this.$emit('input', val);
            this.$emit('change', val);
        }
    }
});
/**
 * 设置链接
 */
Vue.component('el-link-select', {
    template:`
    <div class="el-link-select">
        <el-input v-model="title" style="width:calc(100% - 112px);margin-right: 10px;" disabled></el-input>
        <el-button @click="dialog = true" plain>设置链接</el-button>
        <el-dialog v-if="dialog" class="el-link-dialog" top="20px" title="设置链接" width="600px" :visible.sync="dialog" :close-on-click-modal="false" append-to-body>
            <el-form :model="valueForm" ref="valueForm" :rules="rules" label-width="150px" :validate-on-rule-change="false" @submit.native.prevent>
                <el-form-item label="链接类型：" prop="type">
                    <el-radio-group v-model="linkForm.type" @change="typeChange()">
                        <el-radio v-for="(item, index) in typeList" :label="String(index)" :key="index">
                            {{item}}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <template v-if="linkForm.type == 1">
                <el-form-item label="链接：" prop="url">
                    <el-input v-model="valueForm.url" placeholder="输入链接如果不带http://或https://，则系统会自动加上admin_url()函数"></el-input>
                </el-form-item>
                </template>
                <template v-if="linkForm.type == 2">
                    <el-form-item label="类型：" prop="table">
                        <el-select placeholder="请选择类型" v-model="valueForm.table" @change="detailsListSearch()" filterable>
                            <el-option v-for="(item, index) in tableList" :label="item.title" :value="item.table"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-if="valueForm.table != ''" label="链接：" prop="details">
                        <el-select value-key="id" placeholder="请选择链接，输入标题远程搜索" v-model="valueForm.details" reserve-keyword filterable remote :remote-method="detailsListSearch">
                            <el-option v-for="(item, index) in detailsList" :key="index" :label="item.title" :value="item"></el-option>
                        </el-select>
                    </el-form-item>
                </template>
                <template v-if="linkForm.type == 3">
                    <el-form-item label="分类：" prop="catalog">
                        <el-select v-model="valueForm.catalog" value-key="id" placeholder="请选择分类" filterable>
                            <el-option v-for="(item, index) in catalogList" :key="index" :label="item.title" :value="item">
                                <span v-html="item.treeString"></span>
                                {{ item.title }}
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="锚点：" prop="anchor">
                        <el-input v-model="valueForm.anchor" placeholder="如：#about"></el-input>
                    </el-form-item>
                </template>
                <el-form-item>
                    <el-button size="small" type="primary" @click="determine">确定</el-button>
                    <el-button size="small" @click="dialog = false">取消</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>`,
    props: {
        value: {
            type: Object,
            default: {},
        },
    },
    data() {
        return {
            url: "config/link",
            catalogUrl: "catalog/query",
            linkForm: {},
            linkDefault:{
                type: "0",
                value: [
                    {},
                    {url: ""},
                    {table: "", details: ""},
                    {catalog: "", anchor: ""},
                ],
            },
            dialog: false,
            typeList: ['无', '自定义链接', '详情链接', '分类链接'],
            tableList: [],
            detailsList: [],
            catalogList: [],
            title: "",
        }
    },
    created() {
        this.linkForm = JSON.stringify(this.value) === "{}" || this.value.length === 0 ? this.linkDefault : this.value;
        this.init();
    },
    computed: {
        valueForm() {
            return this.linkForm['value'][this.linkForm.type];
        },
        rules() {
            let rules = {};
            switch(this.linkForm.type){
                case ("1"):
                    rules = {}
                    break;
                case ("2"):
                    rules = {
                        table: [
                            { required: true, message: '请选择类型', trigger: 'blur' },
                        ],
                        details: [
                            { required: true, message: '请选择链接', trigger: 'blur' },
                        ],
                    }
                    break;
                case ("3"):
                    rules = {
                        catalog: [ { required: true, message: '请选择分类', trigger: 'blur' } ]
                    }
                    break;
            }
            return rules;
        },
    },
    methods: {
        /**
         * 初始化
         */
        init() {
            this.catalogListSearch();
            this.tableListSearch();
            this.detailsListSearch();
            this.titleSearch();
        },
        /**
         * 标题
         */
        titleSearch() {
            switch(this.linkForm.type){
                case ("0"):
                case ("1"):
                    this.title = this.valueForm['url'];
                    break;
                case ("2"):
                    this.title = this.valueForm['details'] !== '' ? this.valueForm['details']['title'] : '';
                    break;
                case ("3"):
                    let title = this.valueForm['catalog'] !== '' ? this.valueForm['catalog']['title'] : '';
                    this.title = title + this.valueForm['anchor'];
                    break;
            }
        },
        /**
         * 类型改变
         */
        typeChange() {
            this.$refs.valueForm.clearValidate();
            this.init();
        },
        /**
         * 分类搜索
         */
        catalogListSearch() {
            let self = this;
            if (this.linkForm.type == '3' && this.catalogList.length == 0) {
                request.post(self.catalogUrl, {theme: theme}, function(res) {
                    self.catalogList = tree.convertString(res.data);
                });
            }
        },
        /**
         * 表搜索
         */
        tableListSearch() {
            let self = this;
            if (this.linkForm.type == '2' && this.tableList.length == 0) {
                request.post(self.url, {}, function(res) {
                    self.tableList = res.data;
                });
            }
        },
        /**
         * 表详情搜索
         */
        detailsListSearch(keyword = "") {
            let self = this;
            if (this.linkForm.type == '2' && self.valueForm.table != '') {
                request.post(self.url, {table: self.valueForm.table, keyword: keyword}, function(res) {
                    self.detailsList = res.data;
                });
            }
        },
        /**
         * 确定
         */
        determine() {
            this.$refs.valueForm.validate((valid) => {
                if (valid) {
                    this.titleSearch();
                    this.$emit('input', this.linkForm);
                    this.dialog = false;
                } else {
                    return false;
                }
            });
        },
    },
}) 
/**
 * 自定义字段组件
 */
Vue.component('el-field', {
    template:`
    <div class="el-field" :style="{display: ifset ? 'flex' : 'block'}">
        <div class="el-field-push" v-if="ifset">
            <draggable class="add-draggable" v-model="field" v-bind="addDraggable" :clone="addItem">
                <div v-for="(item, index) in field" class="el-field-move-item">
                    <i class="iconfont" :class="item.icon"></i>
                    <div class="title">{{item.label}}</div>
                </div>
            </draggable>
        </div>
        <el-form class="el-field-content" :class="{notset: ifset == false}" :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
            <draggable :class="{empty: list.length == 0 && ifset}" v-model="list" v-bind="draggable">
                <el-form-item v-for="(item, index) in list" :key="index" class="el-form-draggable">
                    <template slot="label">
                        <el-tooltip placement="top" :content="formVariable(item)">
                            <div>{{item.label}}：</div>
                        </el-tooltip>
                        <div class="el-field-button" v-if="ifset">
                            <el-tooltip content="拖放排序" placement="top" >
                                <el-button size="mini" class="rank" type="info" icon="el-icon-rank" circle></el-button>
                            </el-tooltip>
                            <el-tooltip content="设置" placement="top">
                                <el-button size="mini" type="primary" icon="el-icon-s-tools" circle @click="setItem(item, index)"></el-button>
                            </el-tooltip>
                            <el-tooltip content="删除" placement="top">
                                <el-button size="mini" type="danger" icon="el-icon-close" circle @click="removeItem(item,index)"></el-button>
                            </el-tooltip>
                        </div>
                    </template>
                    <component v-if="show" v-model="item.type.value" :is="item.type.is" :key="item.type.field" :type="item.type.type"></component>
                </el-form-item>
            </draggable>
        </el-form>
        <el-dialog :visible.sync="setShow" title="字段设置" width="500px" top="30px" :close-on-click-modal="false" append-to-body>
            <el-form ref="setForm" :model="setForm" :rules="rules" :label-width="labelWidth" @submit.native.prevent>
                <el-form-item label="字段标题：" prop="label"> 
                    <el-input v-model="setForm.label" placeholder="如：内容"></el-input>
                </el-form-item>
                <el-form-item label="字段变量：" prop="field">
                    <el-input v-model="setForm.field" placeholder="如：content"></el-input>
                </el-form-item>
                <el-form-item> 
                    <el-button size="small" type="primary" @click="changeItem()">确 定</el-button>
                    <el-button size="small" @click="setShow = false">取 消</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
    `,
    props: {
        labelWidth: {
            type: String,
            default: '120px',
        },
        labelPosition: {
            type: String,
            default: 'right',
        },
        variable: {
            type: String,
            default: '',
        },
        ifset: {
            type: Boolean,
            default: true,
        },
        repeat: {
            type: Array,
            default: [],
        },
        value: {
            type: Array,
            default: [],
        },
    },
    data() {
        return {
            show: true,
            list: this.value,
            field: common.formType(),
            draggable: {
                handle: '.rank',
                animation: 300,
                forceFallback: true,
                group:"people"
            },
            addDraggable: {
                animation: 300,
                forceFallback: true,
                sort: false,
                group: {name: 'people', pull: 'clone', put: false},
            },
            rules: {
                label: [
                    { required: true, message: '请填写字段标题', trigger: 'blur' },
                ],
                field: [
                    { required: true, message: '请输入字段变量', trigger: 'blur' },
                    { pattern: /^[a-zA-Z][a-zA-Z0-9_]*$/, message: '以字母开头只能输入字母、下划线、数字', trigger: 'blur' },
                ],
                type: [
                    { required: true, message: '请选择字段类型', trigger: 'blur' },
                ],
            },
            setForm: {},
            setShow: false,
            setIndex: 0,
        }
    },
    methods: {
        /**
         * 添加
         */
        addItem(item) {
            let arr         = {};
            arr.field      = common.id(6);
            arr.label      = '未命名';
            arr.type       = item;
            return JSON.parse(JSON.stringify(arr));
        },
        /**
         * 删除
         */
        removeItem(item,index) {
            let self = this;
            self.list.splice(index, 1);
            if (item.type.is == 'el-array') {
                self.show = false;
                self.$nextTick(() => {
                    self.show = true;
                })
            }
        },
        /**
         * 改变
         */
        changeItem() {
            this.$refs.setForm.validate((valid) => {
                if (valid) {
                    this.$set(this.list, this.setIndex, this.setForm);
                    this.setShow = false;
                } else {
                    return false;
                }
            });
        },
        /**
         * 设置
         */
        setItem(item, index) {
            this.setIndex = index;
            this.setForm  = JSON.parse(JSON.stringify(item));
            this.setShow  = true;
        },
        /**
         * 表单提示变量
         */
        formVariable(item) {
            let raw = item.type.is == 'el-editor' ? '|raw' : '';
            return this.variable === '' ? item.field : '{$' + this.variable + '.' + item.field + raw +'}';
        },
    },
    watch: {
        value(val) {
            this.list = val;
        },
        list(val) {
            this.value = val;
            this.$emit('input', val);
        }
    }
})
/**
 * 参数组件
 */
Vue.component('el-parameter', {
    template:`
    <div class="el-parameter">
        <el-button type="primary" size="small" @click="pushData()">新增一行</el-button>
        <el-input v-model="keyword" v-if="search" style="width:400px" size="small" prefix-icon="el-icon-search" placeholder="请输入关键词搜索"></el-input>
        <draggable v-model="searchList" v-bind="draggable">
            <template v-for="(item, index) in searchList" :key="index">
                <div class="el-parameter-item">
                    <el-input v-model="item.title" placeholder="请输入标题" class="title"></el-input>
                    <el-input v-model="item.value" placeholder="请输入内容" class="value"></el-input>
                    <el-button size="mini" v-if="rank" class="rank" type="info" icon="el-icon-rank" circle></el-button>
                    <el-button size="mini" v-if="push" type="primary" icon="el-icon-plus" @click="pushData(index)" circle></el-button>
                    <el-button size="mini" type="danger" icon="el-icon-close" @click="removeData(index)" circle></el-button>
                </div>
            </template>
        </draggable>
    </div>
    `,
    props: {
        value: {
            type: Array,
            default: [],
        },
        rank: {
            type: Boolean,
            default: true,
        },
        push: {
            type: Boolean,
            default: true,
        },
        search: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            list: this.value,
            draggable: {
                handle: '.rank',
                animation: 300,
                forceFallback: true,
            },
            keyword: '',
        }
    },
    computed: {
        searchList() {
            let list = [];
            let self = this;
            self.list.forEach(function (item, index) {
                if (item.title.indexOf(self.keyword) != -1 || item.value.indexOf(self.keyword) != -1) {
                    list.push(item)
                }
            });
            return list;
        },
    },
    methods: {
        pushData(index = "") {
            let row = {title: '', value: ''};
            index === "" ? this.list.unshift(row) : this.list.splice(index + 1, 0, row);
        },
        removeData(index) {
            this.list.splice(index, 1);
        },
    },
    watch: {
        value(val) {
            this.list = val;
        },
        list(val) {
            this.value = val;
            this.$emit('input', val);
        }
    }
})
/**
 * 编辑器默认是textarea
 */
Vue.component('el-catalog-select', {
    template: `
    <el-select class="el-catalog-select" v-model="content" placeholder="请选择分类" filterable>
        <el-option label="未选择" :value="0"></el-option>
        <el-option v-for="(item, index) in catalogList" :key="index" :label="item.title" :value="item.id">
            <span v-html="item.treeString"></span>
            {{ item.title }}
        </el-option>
    </el-select>
    `,
    props: {
        value: {
            type: Number,
        },
    },
    data() {
        return {
            catalogList: [],
            catalogUrl: "catalog/query",
            content: this.value,
        }
    },
    created() {
        let self = this;
        request.post(self.catalogUrl, {theme: theme}, function(res) {
            self.catalogList = tree.convertString(res.data);
        });
    },
    watch: {
        value(val) {
            this.content = val;
        },
        content(val) {
            this.value = val;
            this.$emit('input', val);
        },
    }
})
/**
 * 编辑器默认是textarea
 */
Vue.component('el-editor', {
    template: `
        <div class="el-editor">
            <el-input type="textarea" v-model="content" :rows="10"></el-input>
        </div>
    `,
    props: {
        value: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            content: this.value,
        }
    },
    watch: {
        value(val) {
            this.content = val;
        },
        content(val) {
            this.value = val;
            this.$emit('input', val);
        },
    }
})