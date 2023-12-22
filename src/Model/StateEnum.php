<?php
 
namespace App\Model;
 
enum StateEnum: string {
    case DRAFT = 'Draft';
    case PUBLISHED = 'Published';
    case REJECTED = 'Rejected';
}