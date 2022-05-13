<?php 

function list_dropbox_tokens($dropbox_tk_id = 0){


    $where = $dropbox_tk_id > 0 ? " WHERE dropbox_tk.id = $dropbox_tk_id;" : ";";
    
    $query = _query(
    "SELECT 
        dropbox_tk.id as token_id,
        dropbox_tk.account_email as email,
        dropbox_tk.limit_size,
        (SELECT 
            sum(max_byte_cloud) 
            FROM client_path 
            WHERE dropbox_tk_id = token_id
        ) as total_used
    FROM dropbox_tk 
    $where");
    
    $total = $query->rowCount();
    if($total == 0) _error();

    return _response($total > 1 ? $query->fetchAllAssoc() : $query->fetchAssoc());
        
}

