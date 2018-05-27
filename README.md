# common_models
A model that summarizes Simple crud processing of Ci_model of Codeigniter
====

## Description
To prevent enlargement of model,
It is made to be able to use commonly used process of model generically.

## Usage
- Create a model and copy it, or download the file and put it under the model directory
```
application 
  └ models
      ├ Crud_model.php
```

- Load model
```
$this->load->model('crud_model');
```

- setter(optional)
```
# where clause

# set where clause($where_clause is 'where' or 'like')
# $target_column array and string are OK
$this->crud_model->set_where($target_column, $where_clause = 'where')

# join clause

# set join clause
# Same as join in Codeigniter's query builder class
				 ->set_join($table, $join_column, $type = 'inner')
```

- Finally call record acquisition function
```
				 ->get_lists($tbl_name, $limit, $offset, $sort = []);
```

- main function
```
# obtaining data for pagination
get_lists($tbl_name, $limit, $offset, $sort = [])

# count record
count_records($tbl_name)

# retrieve multiple or single records
get_record($tbl_name, $is_rows = true, $sort = [], $limit = 0)

# insert record
add_record($tbl_name, $params)

# update record
update_record($tbl_name, $params = [], $target = [])

# delete record
delete_record($tbl_name, $target = [])

# get column name
get_column_name($tbl_name)

# get next id
next_id($tbl_name)

```