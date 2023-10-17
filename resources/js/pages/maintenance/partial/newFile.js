import rf from "requestfactory";
import moment from "moment";
import RemoveErrorsMixin from "common/RemoveErrorsMixin";
import Const from "common/Const";
import Utils from "common/Utils";
import { chain, cloneDeep, isEmpty, size, debounce } from "lodash";

export default (await import('vue')).defineComponent({
components: {
BinConfigures,
VueTimepicker,
Datepicker,
},

props: {
data: {
type: Object,
default: null,
},
},

mixins: [RemoveErrorsMixin],

data() {
return {
inputForm: {
spare_id: null,
min: null,
max: null,
critical: null,
description: null,
configures: [],
},
spares: [],
items: [],
};
},

computed: {
showConfigures() {
return !isEmpty(this.inputForm.configures);
},

disableQuantity() {
return false;
},

visibleBinConfigure() {
return (
this.inputForm.has_batch_no ||
this.inputForm.has_serial_no ||
this.inputForm.has_charge_time ||
this.inputForm.has_calibration_due ||
this.inputForm.has_expiry_date ||
this.inputForm.has_load_hydrostatic_test_due
);
},
},

watch: {
"inputForm.spare_id"(newValue) {
const item = chain(this.spares)
.filter((record) => record.id === newValue)
.head()
.value();

if (!item) {
return;
}

this.inputForm = {
...this.inputForm,
has_batch_no: item.has_batch_no,
has_serial_no: item.has_serial_no,
has_charge_time: item.has_charge_time,
has_calibration_due: item.has_calibration_due,
has_expiry_date: item.has_expiry_date,
has_load_hydrostatic_test_due: item.has_load_hydrostatic_test_due,
};
},

"inputForm.quantity": debounce(function () {
this.initConfigures();

const limit = parseInt(this.inputForm.quantity) || 0;
if (!limit) {
return;
}
this.inputForm.configures = chain(this.inputForm.configures)
.take(limit)
.value();
}, 300),
},

mounted() {
this.inputForm = {
...this.inputForm,
...this.data, : .inputForm,
spare_id: this.data.spares.length > 0 ? this.data.spares[0].id : null,
critical: this.data.spares[0].critical || 0,
description: this.data.spares[0].description || null,
min: this.data.spares[0].min || 1,
max: this.data.spares[0].max || 1,
quantity: this.data.spares[0].quantity || 1,
};
this.initConfigures();
rf.getRequest("AdminRequest").getBinId(this.data.id);
this.items = this.data.spares.map((i) => ({
...i,
spare_id: i.id,
quantity: this.data.quantity,
critical: this.data.critical,
min: this.data.min,
max: this.data.max,
configures: this.data.configures
}));

this.getSpares();
},

methods: {
showDeleteModal(item) {
const _handler = () => {
this.items = this.items.filter((i) => i.spare_id !== item.id);
};
this.confirmAction({ callback: _handler, message: "Do you want to delete?" });
},

initConfigures() {
// const limit = parseInt(this.inputForm.quantity) || 0
const limit = 1;
const currentSize = size(this.inputForm.configures);
const needMore = limit - currentSize < 0 ? 0 : limit - currentSize;

const newData = Array(needMore).fill({
batch_no: null,
serial_no: null,
has_charge_time: null,
charge_time: null,
has_calibration_due: null,
calibration_due: null,
has_expiry_date: null,
expiry_date: null,
has_load_hydrostatic_test_due: null,
load_hydrostatic_test_due: null,
});

this.inputForm.configures = chain(this.inputForm.configures || [])
.concat(newData)
.map((item, index) => {
return {
...item,
scope: `row-${index + 1}`,
input_charge_time: Utils.stringTime2Object(item.charge_time),
calibration_due: Utils.utcToClient(
item.calibration_due,
Const.DATE_PATTERN
),
expiry_date: Utils.utcToClient(
item.expiry_date,
Const.DATE_PATTERN
),
load_hydrostatic_test_due: Utils.utcToClient(
item.load_hydrostatic_test_due,
Const.DATE_PATTERN
),
};
})
.value();
},

getSpares() {
const params = { no_pagination: true };
rf.getRequest("AdminRequest")
.getSpares(params)
.then((res) => {
this.spares = chain(res.data || [])
// .filter(item => item.type !== Const.ITEM_TYPE.EUC.value)
.map((item) => {
return {
...item,
label: item.name,
code: item.id,
};
})
.value();
});
},

async onClickSave() {
this.resetError();

await this.$validator.validateAll();

if (this.$refs.binConfigures) {
await this.$refs.binConfigures.validateData();
}

if (this.errors.any()) {
return;
}

const toUTc = (date) => {
return date ? new moment(date).utc().format(Const.DATE_PATTERN) : null;
};

chain(this.inputForm.configures)
.each((item) => {
(item.charge_time = Utils.objTime2String(item.input_charge_time)),
(item.calibration_due = toUTc(item.calibration_due));
item.expiry_date = toUTc(item.expiry_date);
item.load_hydrostatic_test_due = toUTc(
item.load_hydrostatic_test_due
);
})
.value();

this.submitRequest(this.inputForm)
.then((res) => {
this.showSuccess("Successfully!");
// this.inputForm = {};
// Reset the input form
this.inputForm = {
...this.inputForm,
...this.data,
critical: this.data.critical || 0,
min: this.data.min || 1,
max: this.data.max || 1,
quantity: this.data.quantity || 1,
};
this.resetError();
this.$emit("item:saved", res.data);
})
.catch((error) => {
this.processErrors(error);
});
},

submitRequest(data) {
const params = cloneDeep(data);
return rf.getRequest("AdminRequest").updateBin(data);
},

onAddItem() {
const spare = this.spares.find((i) => i.id == this.inputForm.spare_id);
const existingItem = this.items.find(
(item) => item.spare_id === spare.id
);
if (existingItem) {
this.showError("Duplicated! This item was added!");
} else {
this.items.push({
...this.inputForm,
name: spare.name,
});
}

this.updateListQuantitiesMinMax();
},

updateListQuantitiesMinMax() {
if (this.items.length > 0) {
const totalQuantity = this.items.reduce(
(acc, item) => item.quantity,
0
);
const minValues = this.items.map((item) => item.min);
const maxValues = this.items.map((item) => item.max);
const min = Math.min(...minValues);
const max = Math.max(...maxValues);

this.items.forEach((item) => {
item.quantity = totalQuantity;
item.min = min;
item.max = max;
});
}
},

onClearList() {
this.items = [];
},
// onSaveData() {
//   const data = {
//     ...this.items[0],
//     ...this.inputForm,
//     spare_id: this.items.map((i) => i.spare_id),
//     configures: this.inputForm.configures.map((i) => ({
//       ...i,
//       charge_time: i.has_charge_time
//         ? `${i.input_charge_time.HH}:${i.input_charge_time.mm}`
//         : null,
//     })),
//   };
//   return rf
//   .getRequest("AdminRequest")
//   .updateBin(data)
//   .then((res) => {
//     this.showSuccess("Successfully!");
//     this.$emit("item:saved", res.data);
//   })
//   .catch(() => this.processErrors("Fail"));
// },
},
});
