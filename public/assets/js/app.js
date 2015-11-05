;(function($, angular) {

	var app = angular.module('App', ['ui.bootstrap', 'tg.dynamicDirective', 'ui.sortable']);

	app.run(function($rootScope) 
	{
		$rootScope.uniqId = function() {
		  function s4() {
		    return Math.floor((1 + Math.random()) * 0x10000)
		      .toString(16)
		      .substring(1);
		  }
		  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
		    s4() + '-' + s4() + s4() + s4();
		};
	});

	app.controller('SubjectController', function($scope, $http, $sce) 
	{
		
		$scope.grades = [];
		$scope.sessions = [];

		$scope.init = function() {
			if (typeof window.grades != 'undefined' && angular.isArray(window.grades))
				$scope.grades 	= window.grades;
			if (typeof window.sessions != 'undefined' && angular.isArray(window.sessions))
				$scope.sessions = window.sessions;
		};
		
		$scope.addGrade = function() {
			$scope.grades.push({
				name: '',
				percent: 0,
				minimum: 0
			});
		}

		$scope.removeGrade = function($index) {
			$scope.grades.splice($index, 1);
		};

		$scope.addSession = function() {
			$scope.sessions.push({
				name: '',
				type: '',
				description: ''
			});
		};

		$scope.removeSession = function($index) {
			$scope.sessions.splice($index, 1);
		};
	});

	app.controller('ProgramController', function($scope, $http, $sce) {
		
		$scope.periods = [
			{
				id: $scope.uniqId(),
				name: 'Period 1',
				type: 'period',
				weight: 1
			}
		];

		/**
		 * Configs for ui-sortable
		 * @type {Object}
		 */
		$scope.sortableOptions = {
		    connectWith: ".sortable",
		    placeholder: "ui-state-highlight",
		};

		$scope.subjects = [];

		$scope.active = {};

		$scope.alreadyAddedSubject = [];

		$scope.init = function() {
			if (typeof window.subjects != 'undefined' && angular.isObject(window.subjects))
				$scope.subjects 	= window.subjects;

			if (typeof window.periods != 'undefined' && angular.isArray(window.periods))
				$scope.periods 			= window.periods;

			$scope.setAlreadyAddedSubject();

			console.log($scope.alreadyAddedSubject);
		};

		$scope.setActiveField = function (field) {
			$scope.active = field;
		};

		$scope.addSubject = function(id) {
			$scope.periods.push({
				id: id,
				type: 'subject'
			});

			$scope.alreadyAddedSubject.push(id);
		};

		$scope.addPeriod = function() {
			$scope.periods.push({
				id: $scope.uniqId(),
				name: 'Period',
				type: 'period',
				weight: 1
			});
		};

		$scope.removeItem = function($index) {
			var item = $scope.periods[$index];

			$scope.periods.splice($index, 1);
			
			if (item.type === 'subject') {
				var index = $scope.alreadyAddedSubject.indexOf(item.id);
				$scope.alreadyAddedSubject.splice(index, 1);	
			}
		};

		$scope.setAlreadyAddedSubject = function() {
			angular.forEach($scope.periods, function(period) {
				if (period.type === 'subject')
					$scope.alreadyAddedSubject.push(period.id);
			});
		};
	});

	app.controller('MetaController', function($scope) {
		
		$scope.meta 	= [];
		$scope.object 	= '';
		$scope.object_id = 0;

		$scope.init = function() {
			$scope.meta = window.meta;
			$scope.object = window.object;
			$scope.object_id = window.object_id;
		};

		$scope.addMeta = function() {
			$scope.meta.push({
				key: '',
				value: ''
			});
		};

		$scope.removeMeta = function($index) {
			$scope.meta.splice($index, 1);
		};
	});

	app.controller('ClassController', function($scope, $http) {
		
		$scope.students = [
			{
				id: 1,
				name: 'Tan Nguyen',
				photo: '0/01/Flag_of_California.svg/45px-Flag_of_California.svg.png'
			},
			{
				id: 2,
				name: 'Anh Tran',
				photo: 'e/ef/Flag_of_Hawaii.svg/46px-Flag_of_Hawaii.svg.png'
			}
		];

		$scope.selectedStudents = [];

		$scope.periods = null;

		$scope.subjects = [];

		$scope.selectedProgram = null;

		$scope.selectedPeriods = [];

		$scope.selectedSubjects = [];

		$scope.init = function() {
			//$scope.students = window.students;
			//$scope.selected = window.selected;
		};

		$scope.addStudent = function($item, $model, $label) {
			$scope.selectedStudents.push($item);

			var students = [];
			
			angular.forEach($scope.students, function(student) {
				if (student.id !== $item.id)
					students.push(student);
			});

			$scope.students = students;
			$scope.selected = [];
		};

		$scope.showPeriods = function() {
			if (typeof $scope.selectedProgram == 'undefined' || $scope.selectedProgram <= 0)
				return;

			$http.get('/programs/periods/' + $scope.selectedProgram).
				success(function(data, status, headers, config) {
			    	$scope.periods = data;
			  	}).
			  	error(function(data, status, headers, config) {
			    	//
			});
		};

		$scope.showSubjects = function() {
			if ($scope.selectedPeriods.length == 1) {
				angular.forEach($scope.periods, function(period) {
					if (period.id === $scope.selectedPeriods[0]) {
						$scope.subjects = period.subjects;
						return;
					}
				});
			}
		};

		$scope.addSubject = function(a){
			console.log($scope.selectedSubjects);
		};

		$scope.removeStudent = function(student) {
			$scope.selectedStudents.splice($index, 1);
		};
	});

})(jQuery, angular);