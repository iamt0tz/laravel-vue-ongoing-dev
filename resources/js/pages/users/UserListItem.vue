<script setup>

import {ref} from 'vue';


defineProps({
    user: Object,
    index: Number,
});

const confirmUserDeletion = (user) =>{
  userIdBeingDeleted.value = user.id;
$('#deleteUserModal').modal('show');
};


const userIdBeingDeleted = ref(null);


const deleteUser = () =>{
  axios.delete(`/api/users/${userIdBeingDeleted.value}`)
  .then (() =>{
    $('#deleteUserModal').modal('hide');
    users.value = users.value.filter(user=>user.id !== userIdBeingDeleted.value);
    toastr.success('User deleted successfully!');
  });
};


</script>

<template>
<tr >
        <td>{{ index +1 }}</td>
        <td>{{ user.name }}</td>
        <td>{{ user.email }}</td>
        <td>{{ user.created_at }}</td>
        <td>{{ user.role }}</td>
        <td>
            <a href="" @click.prevent="editUser(user)"><i class="fa fa-edit"></i></a>
            <a href="" @click.prevent="confirmUserDeletion(user)"><i class="fa fa-trash text-danger ml-2"></i></a>
        </td>

    </tr>

    

    <div class="modal fade" id="deleteUserModal">
        <div class="modal-dialog deleteUserModal">
          <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title">
                <h2>Delete User</h2>
              </h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <!-- modal body ========================== -->
            <div class="modal-body">
              
            <h5>Are you sure you want to delete this user?</h5>
            </div>

            <div class="modal-footer">
              <button  type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
              <button @click.prevent="deleteUser" type="button" class="btn btn-danger">Delete User</button>
            </div>

            </div>  <!-- /.modal-body -->
          
            
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>

</template>