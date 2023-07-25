<script setup>  
import axios from 'axios';
import {ref, onMounted, reactive, watch } from 'vue';
import {Form, Field, useResetForm } from 'vee-validate';
import * as yup from 'yup';
import {useToastr} from '../../toastr.js';
import UserListItem from './UserListItem.vue'; 
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

const toastr = useToastr();
const users = ref({'data': []});
const editing = ref(false);
const formValues =ref();
const form = ref(null);


 

const getUsers = (page = 1) =>{
    axios.get(`/api/users?page=${page}`)
    .then((response) => {
        users.value = response.data;
    })

};

const createUserSchema = yup.object ({
  name: yup.string().required(),
  email: yup.string().email().required(),
  password: yup.string().required().min(8),
});

const editUserSchema = yup.object({
    name: yup.string().required(),
    email: yup.string().email().required(),
    password: yup.string().when((password, schema) => {
        return password ? schema.required().min(8) : schema;
    }),
});

const createUser = (values, {resetForm,setErrors}) => { 
  axios.post('/api/users', values)
  .then((response) =>{
    users.value.unshift(response.data); 
        $('#userFormModal').modal('hide');
        resetForm();
        
      toastr.success('User created successfully.');
  })
  .catch((error) =>{
    if(error.response.data.errors)
    {
      setErrors(error.response.data.errors);
    }
  })
}; 

const addUser = () => {
  editing.value=false;
  $('#userFormModal').modal('show');
};

const editUser =(user) =>{  
  // console.log(user);
  editing.value=true;
  form.value.resetForm();
  $('#userFormModal').modal('show');
  formValues.value ={
    id: user.id,
    name: user.name,
    email: user.email,
  }; 
};

const updateUser = (values,{setErrors}) => {
  axios.put('/api/users/' + formValues.value.id, values)
    .then((response) => {
      const index = users.value.findIndex(user => user.id === response.data.id);
      users.value[index] = response.data;
      $('#userFormModal').modal('hide');
      toastr.success('User updated successfully.');
    }).catch((error) => { 
      setErrors(error.response.data.errors);
      console.log(error);
    });
  // console.log(values);
}

const handleSubmit = (values, actions) =>{
  // console.log(actions);
  console.log(actions);
  
  if(editing.value){
    updateUser(values, actions);
  } else {
    createUser(values, actions);
  }
}  

const userDeleted = (userId)=>{
  users.value = users.value.filter(user=>user.id !== userId);
};

const searchQuery = ref(null);
const search = () =>
{
  axios.get('/api/users/search',{
    params: {
      query: searchQuery.value
    }
  })
  .then(response => {
    users.value =  response.data;
  })
  .catch(error => {
    console.log(error);
  })
};

watch(searchQuery,  () =>{
  search();
});

onMounted(()=>{
    getUsers(); 
});
</script>

<template>
    
    <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">  
                <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Users List </h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Users List</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid"> 
        <div class="d-flex justify-content-between">
          
        <button @click="addUser" type="button" class="btn btn-primary p-2 m-2"  >
                        Add New User
                    </button>
                    <div>
                      <input type="text" v-model="searchQuery" class="form-control" placeholder="Search..." />
                      
                    </div>
        </div>
        <div class="table-responsive">
                  <table class="table m-0">
                    <thead>
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Registered Date</th>
                      <th>Role</th>
                      <th>Options</th>
                    </tr>
                    </thead>
                    <tbody v-if="users.data.length >0"> 
                        <UserListItem  v-for="(user, index) in users.data" 
                        :key="user.id"
                        :user=user
                        :index=index
                        @edit-user="editUser"
                        @user-deleted ="userDeleted"
                        
                        />
                    </tbody>
                    <tbody v-else >
                      <tr>
                        <td colspan="6" class="text-center">No results found.... </td>
                      </tr>
                    </tbody>
                  </table>

                  <Bootstrap4Pagination
                :data="users"
                  @pagination-change-page="getUsers"
              />
                </div>
                <!-- /.table-responsive -->
 
      </div><!-- /.container-fluid -->
    </div>
</div>
</div>
</div>
</div>


<div class="modal fade" id="userFormModal">
        <div class="modal-dialog userFormModal">
          <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title">
                <span v-if="editing">Edit User</span>
                <span v-else>Add new user</span>
              </h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <!-- FORM ========================== -->

            <Form ref="form"
             @submit="handleSubmit"
             :validation-schema="editing ? updateUser : createUser" 
             v-slot="{ errors }"
             :initial-values="formValues">
            
            <div class="modal-body"> 
              
               
                  <div class="form-group row">
                    <label for="name" class="col-sm-2 col-form-label">Name</label>
                    <div class="col-sm-10">
                      <Field name="name" type="text" class="form-control" :class="{'is-invalid': errors.name}"
                      id="name" placeholder="Enter full name" />
                      <span class="invalid-feedback">{{ errors.name }}</span>
                    </div>
                  </div>

                  <div class="form-group row">
                    <label for="email" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                      <Field name="email" type="email" class="form-control" :class="{'is-invalid': errors.email}"
                      id="email" placeholder="Enter a valid email" />
                        <span class="invalid-feedback">{{ errors.email }}</span>
                    </div>
                  </div>

                  <div class="form-group row">
                    <label for="password" class="col-sm-2 col-form-label">Password</label>
                    <div class="col-sm-10">
                      <Field name="password" type="password" class="form-control" :class="{'is-invalid': errors.password}"
                      id="password" placeholder="Enter password" />
                      <span class="invalid-feedback">{{ errors.password }}</span>
                    </div>
                  </div>

                </div>
                <div class="modal-footer justify-content">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Save</button>
           
            
           </div> 
          </Form>
            </div>  <!-- /.modal-body -->
          
            
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->
    



</template>