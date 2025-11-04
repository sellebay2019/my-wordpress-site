import { RouteBase } from "@/types/enums";
import { translate } from "@/utils/helpers";
import Home from "@/views/HostingerTools.vue";

export default [
	{
		path: "/",
		name: RouteBase.HOSTINGER_TOOLS,
		meta: {
			title: translate("routes_tools"),
			headerButton: {
				text: translate("hostinger_tools_open_guide"),
				href: "https://www.hostinger.com/tutorials/how-to-use-hostinger-tools-plugin"
			},
			previewSiteButton: {
				text: translate("hostinger_tools_preview_site")
			},
			editSiteButton: {
				text: translate("hostinger_tools_edit_site")
			}
		},
		component: Home
	}
];
