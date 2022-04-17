use nic_db;

SELECT
	view_page.slug as page_slug,
    view_page.nome as page_nome,
    view_page.icon as page_icon,
    view_subpage.nome as subpage_nome,
    view_subpage.slug as subpage_slug,
    view_subpage.icon as subpage_icon
FROM view_subpage 
	JOIN view_page ON view_page.id = view_subpage.view_page_id 
	JOIN permission_pool ON permission_pool.id = view_subpage.permission_pool_id
	JOIN user_permission ON user_permission.permission_pool_id = permission_pool.id
	JOIN user ON user.id = user_permission.user_id
WHERE user.id = 2 
ORDER BY view_page.main DESC, view_subpage.main DESC;