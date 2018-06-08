import {Component, OnInit} from '@angular/core';
import {ApiService} from '../../../api/api.service';
import {ActivatedRoute} from '@angular/router';
import {ScreenshotsService} from '../screenshots.service';
import {ItemsShowComponent} from '../../items.show.component';
import {Screenshot} from '../../../models/screenshot.model';
import {AllowedActionsService} from '../../roles/allowed-actions.service';

@Component({
    selector: 'app-screenshots-show',
    templateUrl: './screenshots.show.component.html',
    styleUrls: ['../../items.component.scss']
})
export class ScreenshotsShowComponent extends ItemsShowComponent implements OnInit {

    public item: Screenshot = new Screenshot();

    constructor(api: ApiService,
                screenshotService: ScreenshotsService,
                router: ActivatedRoute,
                allowService: AllowedActionsService) {
        super(api, screenshotService, router, allowService);
    }
}
