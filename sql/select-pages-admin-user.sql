use nic_db;

# carregamento de tela inicial
# caso nao seja um usuario master

SELECT 
	view_page.slug as page_slug,
    view_page.nome as page_nome,
    view_page.icon as page_icon,
    view_subpage.nome as subpage_nome,
    view_subpage.slug as subpage_slug,
    user.nome
	FROM view_subpage
    JOIN view_page ON view_page.id = view_subpage.view_page_id
    JOIN permission_pool ON view_subpage.permission_pool_id = permission_pool.id
    JOIN user_permission ON user_permission.permission_pool_id = permission_pool.id
    JOIN user ON user.id = user_permission.user_id
    WHERE user_permission.user_id = 2 ;
    

# para usuarios master
SELECT 
	view_page.slug as page_slug,
    view_page.nome as page_nome,
    view_page.icon as page_icon,
    view_subpage.nome as subpage_nome,
    view_subpage.slug as subpage_slug
    FROM view_subpage
    JOIN view_page ON view_page.id = view_subpage.view_page_id;
    
