import {Component, OnInit, TemplateRef} from '@angular/core';
import {ApiService} from '../../../api/api.service';
import {Project} from "../../../models/project.model";
import {ProjectsService} from "../projects.service";
import {BsModalService} from 'ngx-bootstrap/modal';
import {BsModalRef} from 'ngx-bootstrap/modal/bs-modal-ref.service';
import {Router} from "@angular/router";
import {ItemsListComponent} from "../../items.list.component";
import {Task} from "../../../models/task.model";


@Component({
    selector: 'app-projects-list',
    templateUrl: './projects.list.component.html',
    styleUrls: ['./projects.list.component.scss']
})
export class ProjectsListComponent extends ItemsListComponent implements OnInit {

    itemsArray: Project[] = [];

    constructor(api: ApiService,
                projectService: ProjectsService,
                modalService: BsModalService,) {
        super(api, projectService, modalService);
    }
}