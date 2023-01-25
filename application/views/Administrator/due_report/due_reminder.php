<style>
    .record-table{
		width: 100%;
		border-collapse: collapse;
	}
	.record-table thead{
		background-color: #0097df;
		color:white;
	}
	.record-table th, .record-table td{
		padding: 3px;
		border: 1px solid #454545;
	}
    .record-table th{
        text-align: center;
    }
</style>
<div id="dues">
    <div class="row" style="border-bottom: 1px solid #ccc;">
		<div class="col-md-12">
			<form action="" class="form-inline" @submit.prevent="getResult">
				<div class="form-group">
					<label for="">Date From</label>
					<input type="date" class="form-control" v-model="filter.dateFrom">
				</div>

				<div class="form-group">
					<label for="">Date To</label>
					<input type="date" class="form-control" v-model="filter.dateTo">
				</div>

				<div class="form-group">
					<input type="submit"  class="btn btn-sm btn-info" value="Search" style="height: 28px;margin-bottom: 4px;padding: 0px 10px;">
				</div>
			</form>
		</div>
	</div>

    <div class="row" style="margin-top: 15px;display: none" :style="{display: dues.length > 0 ? '' : 'none'}">
        <div class="col-md-12" style="margin-bottom: 10px;">
			<a href="" @click.prevent="print"><i class="fa fa-print"></i> Print</a>
		</div>

        <div class="col-md-12">
            <div class="table-responsive" id="reportContent">
                <table class="record-table">
                    <thead>
                        <th>SL</th>
                        <th>Invoice</th>
                        <th>Customer Id</th>
                        <th>Customer Name</th>
                        <th>Reminder Date</th>
                        <th>Total Bill</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        <tr v-for="customer in dues">
                            <td class="text-center">{{ customer.sl }}</td>
                            <td class="text-center">{{ customer.SaleMaster_InvoiceNo }}</td>
                            <td class="text-center">{{ customer.Customer_Code }}</td>
                            <td class="text-center">{{ customer.Customer_Name }} </td>
                            <td class="text-center">{{ customer.reminder_date }}</td>
                            <td class="text-right">{{ customer.SaleMaster_TotalSaleAmount }}</td>
                            <td class="text-right">{{ customer.SaleMaster_PaidAmount }}</td>
                            <td class="text-right">{{ customer.SaleMaster_DueAmount }}</td>
                            <td class="text-center">
                                <?php if($this->session->userdata('accountType') != 'u'){?>
                                    <a href="" title="Edit Reminder Date" data-toggle="modal" data-target="#exampleModal" @click.prevent="changeDate(customer.SaleMaster_SlNo)">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                <?php }?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- modal start -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Reminder Date 
                        <button type="button" class="close"  data-dismiss="modal" aria-label="Close">x</button>
                    </h5>
                    
                </div>
                <form @submit.prevent="saveReminderDate">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                <input type="date" class="form-control" v-model="reminder" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- end start -->
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>

<script>
    const app = new Vue({
        el: '#dues',
        data: {
            filter: {
                dateFrom: moment().format('YYYY-MM-DD'),
                dateTo: moment().format('YYYY-MM-DD')
            },
            reminder: moment().format('YYYY-MM-DD'),
            salesId: null,
            dues: [],
        },
        async created() {
            await this.getResult();
        },
        methods: {
            async getResult() {
                await axios.post('/get_due_reminder', this.filter)
                .then(res => {
                    this.dues = res.data.map((item, sl) => {
                        item.sl = sl + 1;
                        return item;
                    });
                })
            },
            async print(){
				let dateText = '';
				if(this.filter.dateFrom != '' && this.filter.dateTo != ''){
					dateText = `Statement from <strong>${this.filter.dateFrom}</strong> to <strong>${this.filter.dateTo}</strong>`;
				}

				let reportContent = `
					<div class="container">
						<div class="row">
							<div class="col-xs-12 text-center">
								<h3>Due Reminder List</h3>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-6">
								
							</div>
							<div class="col-xs-6 text-right">
								${dateText}
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#reportContent').innerHTML}
							</div>
						</div>
					</div>
				`;

				var reportWindow = window.open('', 'PRINT', `height=${screen.height}, width=${screen.width}`);
				reportWindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php');?>
				`);

				reportWindow.document.head.innerHTML += `
					<style>
						.record-table{
							width: 100%;
							border-collapse: collapse;
						}
						.record-table thead{
							background-color: #0097df;
							color:white;
						}
						.record-table th, .record-table td{
							padding: 3px;
							border: 1px solid #454545;
						}
						.record-table th{
							text-align: center;
						}
					</style>
				`;
				reportWindow.document.body.innerHTML += reportContent;
			
                let rows = reportWindow.document.querySelectorAll('.record-table tr');
                rows.forEach(row => {
                    row.lastChild.remove();
                })

				reportWindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				reportWindow.print();
				reportWindow.close();
			},
            changeDate(salesId) {
               this.salesId = salesId
            },
            async saveReminderDate() {
                let data = {
                    salesId: this.salesId,
                    date: this.reminder
                }

                await axios.post('/change_reminder_date', data)
                .then(res => {
                    alert(res.data.message);
                    $('#exampleModal').modal('hide')
                    this.getResult();
                })
                .catch(err => {
                    console.log(err.response.data.message)
                })
            }
        }
    })
</script>