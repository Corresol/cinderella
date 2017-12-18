<?php

namespace App\Libraries;

use Crada\Apidoc\Builder;

class ApidocBuilder extends Builder
{
    public static $mainTpl = '
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            {{ method }} <a data-toggle="collapse" data-parent="#accordion{{ elt_id }}" href="#collapseOne{{ elt_id }}"> {{ route }}</a>
        </h4>
    </div>
    <div id="collapseOne{{ elt_id }}" class="panel-collapse collapse">
        <div class="panel-body">
            <!-- Tab panes -->
            <div class="tab-content">

                <div class="tab-pane active" id="info{{ elt_id }}">
                    <div class="well">
                    {{ description }}
                    </div>
                    <div class="panel panel-default">
                      <div class="panel-heading"><strong>Parameters</strong></div>
                      <div class="panel-body">
                        {{ parameters }}
                      </div>
                    </div>
                    <div class="panel panel-default">
                      <div class="panel-heading"><strong>Return</strong></div>
                      <div class="panel-body">
                        {{ body }}
                      </div>
                    </div>
                    <div class="panel panel-default">
                      <div class="panel-heading"><strong>Errors</strong></div>
                      <div class="panel-body">
                        {{ headers }}
                      </div>
                    </div>
                </div><!-- #info -->

                <div class="tab-pane" id="sandbox{{ elt_id }}">
                    <div class="row">
                        <div class="col-md-12">
                        {{ sandbox_form }}
                        </div>
                        <div class="col-md-12">
                            Response
                            <hr>
                            <div class="col-md-12" style="overflow-x:auto">
                                <pre id="response_headers{{ elt_id }}"></pre>
                                <pre id="response{{ elt_id }}"></pre>
                            </div>
                        </div>
                    </div>
                </div><!-- #sandbox -->

                <div class="tab-pane" id="sample{{ elt_id }}">
                    <div class="row">
                        <div class="col-md-12">
                            {{ sample_response_headers }}
                            {{ sample_response_body }}
                        </div>
                    </div>
                </div><!-- #sample -->

            </div><!-- .tab-content -->
        </div>
    </div>
</div>';

    static $samplePostBodyTpl = '<pre id="sample_post_body{{ elt_id }}">{{ body }}</pre>';

    static $sampleReponseTpl = '
{{ description }}
<hr>
<pre id="sample_response{{ elt_id }}">{{ response }}</pre>';

    static $sampleReponseHeaderTpl = '
<pre id="sample_resp_header{{ elt_id }}">{{ response }}</pre>';

    static $paramTableTpl = '
<table class="table table-hover">
    <thead>
        <tr>
            <th>Code</th>
            <th>Type</th>
            <th>Managed</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        {{ tbody }}
    </tbody>
</table>';

    static $paramContentTpl = '
<tr>
    <td>{{ name }}</td>
    <td>{{ type }}</td>
    <td>{{ nullable }}</td>
    <td>{{ description }}</td>
</tr>';

    static $paramSampleBtnTpl = '
<a href="javascript:void(0);" data-toggle="popover" data-placement="bottom" title="Sample object" data-content="{{ sample }}">
    <i class="btn glyphicon glyphicon-exclamation-sign"></i>
</a>';

    static $sandboxFormTpl = '
        <div class="col-md-6">
    Headers
    <hr/>
    <div class="headers">
    {{ headers }}
    </div>
    </div>
    <div class="col-md-6">
<form enctype="application/x-www-form-urlencoded" role="form" action="{{ route }}" method="{{ method }}" name="form{{ elt_id }}" id="form{{ elt_id }}">
    
    Parameters
    <hr/>
    {{ params }}
    <button type="submit" class="btn btn-success send" rel="{{ elt_id }}">Send</button>
</form></div>';

    static $sandboxFormInputTpl = '
<div class="form-group">
    <input type="text" class="form-control input-sm" id="{{ name }}" placeholder="{{ name }}" name="{{ name }}">
</div>';
}