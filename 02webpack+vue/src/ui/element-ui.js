import Vue from 'vue';
import {
    Button,
    Input,
    InputNumber,
    Dialog,
    Container,
    Header,
    Main,
    Footer,
    Switch,
    Radio,
    RadioGroup,
    Checkbox,
    Cascader,
    Form,
    FormItem,
    Select,
    Option,
    Autocomplete,
    Alert,
    Loading,
    Message,
    MessageBox
} from 'element-ui';

Vue.use(Button);
Vue.use(Autocomplete);
Vue.use(Alert);
Vue.use(Input);
Vue.use(InputNumber);
Vue.use(Dialog);
Vue.use(Container);
Vue.use(Header);
Vue.use(Main);
Vue.use(Footer);
Vue.use(Switch);
Vue.use(Radio);
Vue.use(Button);
Vue.use(Checkbox);
Vue.use(Cascader);
Vue.use(Form);
Vue.use(FormItem);
Vue.use(Select);
Vue.use(Option);
Vue.use(RadioGroup);
Vue.use(Loading.directive);
Vue.prototype.$msgbox = MessageBox;
Vue.prototype.$alert = MessageBox.alert;
Vue.prototype.$confirm = MessageBox.confirm;
Vue.prototype.$prompt = MessageBox.prompt;
Vue.prototype.$message = Message;



