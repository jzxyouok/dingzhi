$.fn.calGraph = function(date, data) {
	var helper = new CalHelper(date, data, this);
	var html = helper.getHtml();
	$(this).html(html);
};

function CalHelper(date, data) {
	this.date = date;
	this.data = data;
	if(date) {
		var arr = date.split('-');
		this.year = arr[0];
		this.month = arr[1];
		this.day = arr[2];
	}
}
CalHelper.prototype = {
	getOneMonthDate: function() {
		var self = this;
		var now = new Date();

		var dateList = [];
		for(var i = 0; i < 31; i++) {
			var dateObj = {};
			var date = new Date();
			date.setFullYear(self.year);
			date.setMonth(this.month - 1);
			date.setDate((i+1));

			//下个月,结束循环
			if(date.getMonth() != (this.month - 1)) {
				break;
			}

			//今天
			if(date.getFullYear() == now.getFullYear() && date.getMonth() == now.getMonth() && date.getDate() == now.getDate()) {
				dateObj.isToday = true;
			}

			dateObj.date = date;
			dateObj.data = data[i];

			dateList.push(dateObj);
		}

		return dateList;
	},
	getGraphDate: function() {
		var self = this;
		var dateList = self.getOneMonthDate();
		var first = dateList[0];
		var last = dateList[dateList.length - 1];
		var dateObj = null;
		var date = null;
		var i = 0;

		//前面补全
		var forCount = first.date.getDay() - 1;
		if(forCount < 0) {
			forCount += 7;
		}
		//count的范围为 0 ~ 6
		for(i = 0; i < forCount; i++) {
			dateObj = {};
			date = new Date();
			date.setFullYear(self.year);
			date.setMonth(this.month - 1);
			date.setDate(-i);

			dateObj.isLastMonth = true;
			dateObj.date = date;
			dateList.unshift(dateObj);
		}

		//后面补全
		forCount = 7 - last.date.getDay();
		if(forCount > 6) {
			forCount = 0;
		}
		//count的范围为 0 ~ 6
		for(i = 0; i < forCount; i++) {
			dateObj = {};
			date = new Date();
			date.setFullYear(self.year);
			date.setMonth(this.month);
			date.setDate((i + 1));

			dateObj.isNextMonth = true;
			dateObj.date = date;
			dateList.push(dateObj);
		}

		var graphData = [];
		var rowData = [];

		dateList.forEach(function(item, index){
			rowData.push(item);

			if(((index + 1) % 7) === 0) {
				graphData.push(rowData);
				rowData = [];
			}
		});
		if(rowData.length > 0) {
			graphData.push(rowData);
		}

		return graphData;
	},
	getHtml: function() {
		var self = this;
		var graphData = self.getGraphDate();

		var html = '<div class="calendar-container">';
		html += '<table>';
		html += '<thead><tr>';
		html += '<td>周一</td><td>周二</td><td>周三</td><td>周四</td><td>周五</td><td class="weekend">周六</td><td class="weekend">周日</td>';
		html += '</tr></thead>';
		html += '<tbody>';

		graphData.forEach(function(row){
			html += '<tr>';
			row.forEach(function(obj){
				if (obj.isLastMonth) {
					html += '<td class="last-month">';
				} else if (obj.isNextMonth) {
					html += '<td class="next-month">';
				} else if (obj.isToday) {
					html += '<td class="today">';
				} else {
					html += '<td>';
				}

				if (obj.isToday) {
					html += '<span class="today-text">今天</span>';
				}
				
				html += '<span class="cal-data">';
				html += obj.date.getDate();
				html += '</span>';

				if (obj.data) {
					var href = obj.data.href ? obj.data.href : 'javascript:void(0);';
					html += '<a href="' + href + '" class="table-data">';
					html += obj.data.text;
					html += '</a>';
				}

				html += '</td>';
			});
			html += '</tr>';
		});

		html += '</tbody>';
		html += '</table>';
		html += '</div>';

		return html;
	}
};

